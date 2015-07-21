<?php
/**
 * This file contains the Job Runner command class
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam;

use Iu\Uits\Webtech\Clam\Model\Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Rhumsaa\Uuid\Uuid;

/**
 * Job Runner command class
 * This class will when executed run all the queued jobs in the database
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class JobRunner extends Command
{
    /** @var An instance of the dependency container */
    private $deps;
    
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("jobs:run")
        ->addOption(
            "no-lock",
            "l",
            InputOption::VALUE_NONE,
            "If set, the task will ignore any other running instances"
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Check to see if this should run at all
         */
        if (!$this->shouldRun($input->getOption("no-lock"))) {
            $output->writeLn("Error: There appears to be an instance of this script running already.");
            return;
        }
        
        /**
         * Add lock file if required
         */
        if (!$input->getOption("no-lock")) {
            $this->setLockFile();
        }
        
        $em = $this->deps["entityManager"];
        
        $query = $em->createQuery("SELECT j from Iu\Uits\Webtech\Clam\Model\Job j where j.state = 'waiting'");
        $jobs = $query->getResult();
        
        foreach ($jobs as $job) {
            /**
             * Set the job state to running, this may be excessive, but what the
             * heck. We can do it, and it would be good to know anyway.
             */
            $job->state = "running";
            $em->persist($job);
            $em->flush();
            
            /**
             * Run the actual clam scan
             */
            $process = new Process($this->buildJobCommand($job));
            $process->setTimeout(36000);
            $process->run();
            
            /**
             * Figure out if the program finished correctly and set the state
             * accordingly.
             * If it's not 0 it screwed up
             */
            if ($process->isSuccessful()) {
                $job->state = "finished";
            } else {
                $job->state = "failed";
            }
            $em->persist($job);
            $em->flush();
            
            /**
             * Parse the results into something useful
             */
            $results = $this->parseOutput($process->getOutput());
            
            /**
             * Create and store the results in the database
             */
            $result = new Result();
            $result->id = Uuid::uuid1();
            $result->completedAt = new \DateTime("now", $this->deps["timezone"]);
            $result->scannedDirectories = $results["summary"]["Scanned directories"];
            $result->scannedFiles = $results["summary"]["Scanned files"];
            $result->infectedFiles = $results["summary"]["Infected files"];
            $result->dataScanned = $results["summary"]["Data scanned"];
            $result->dataRead = $results["summary"]["Data read"];
            $result->executionTime = $results["summary"]["Time"];
            $result->fileResults = $results["files"];
            $em->persist($result);
            
            /**
             * Add the resultId to the job entry
             */
            $job->result = $result->id;
            $em->persist($job);
            
            $em->flush();
            
            /**
             * Send an email to the requested report address (if set)
             */
            if (!is_null($job->reportAddress)) {
                $this->sendEmailReport($job, $result);
            }
        }
        
        /**
         * Delete the lock file if required
         */
        if (!$input->getOption("no-lock")) {
            $this->unsetLockFile();
        }
    }
    
    /**
     * Check to see if this command should be run. It should be run if no lock
     * is true or if the lock file doesn't exist.
     *
     * @param boolean $noLocking Whether there's an execution lock
     * @return boolean Whether this command should execute
     */
    private function shouldRun($noLocking)
    {
        if ($noLocking) {
            return true;
        }
        
        if (!file_exists($this->deps["configMain"]->command->paths->lockFile)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Creates the lock file which will contain the pid of the current exection
     */
    private function setLockFile()
    {
        $pid = getmypid();
        file_put_contents(
            $this->deps["configMain"]->command->paths->lockFile,
            $pid
        );
    }
    
    /**
     * Deletes the lock file
     */
    private function unsetLockFile()
    {
        unlink($this->deps["configMain"]->command->paths->lockFile);
    }
    
    /**
     * Parse output function
     * This function will parse the output from clamscan into a usable array
     *
     * @param string $output The output from clamscan
     * @return array The output parsed into an array
     */
    private function parseOutput($output)
    {
        $parts = preg_split("/^-{11}\sSCAN\sSUMMARY\s-{11}$/m", $output);
            
        /**
         * Deal with the "files" section of the output
         */
        preg_match_all("/(?P<file>.*?)\:\s(?P<status>.*)/", $parts[0], $files, PREG_SET_ORDER);
        foreach ($files as $key => $value) {
            /**
             * We don't want the numerically indexed results or the original
             * string match. I kind of hate that preg_match returns those
             */
            unset($value[0], $value[1], $value[2]);
            
            $files[$key] = $value;
        }
        
        /**
         * Deal with the summary section of the output
         */
        preg_match_all("/(?P<key>.*?)\:\s(?P<value>.*)/", $parts[1], $summaryParts, PREG_SET_ORDER);
        $summary = [];
        foreach ($summaryParts as $summaryPart) {
            $summary[$summaryPart["key"]] = $summaryPart["value"];
        }
        
        return [
            "files" => $files,
            "summary" => $summary
        ];
    }
    
    /**
     * Work out what the command to run should be based on the parameters in the
     * job from the database.
     *
     * @param object $job The job model with data
     * @return string The command to run
     */
    private function buildJobCommand($job)
    {
        $homeRoot = $this->deps["configMain"]->command->paths->homeRoot;
        $directory = $homeRoot . "/" . $job->username;
        
        $command = "clamscan --recursive=yes ";
            
        if (!$job->logAllFiles) {
            $command .= " --infected ";
        }
        
        $excludeDirs = implode(",", $job->excludeDirs);
        if (strlen($excludeDirs)) {
            $dirs = implode(",", $job->excludeDirs);
            $command .= " --exclude-dir={$dirs} ";
        }
        
        $excludeFiles = implode(",", $job->excludeFiles);
        if (strlen($excludeFiles)) {
            $files = implode(",", $job->excludeFiles);
            $command .= " --exclude={$files} ";
        }
        
        $command .= " {$directory}";
        return $command;
    }
    
    /**
     * Send an email report to the email address in the job
     *
     * @param object $job The job object
     * @param object $result The result object
     */
    private function sendEmailReport($job, $result)
    {
        $loader = new \Twig_Loader_Filesystem(__dir__ . "/../../../../../view");
        $twig = new \Twig_Environment($loader, []);
        
        $reportJob = $job->toArray();
        $reportResult = $result->toArray();
        $reportJob["result"] = $reportResult;
        
        $mailConfig = $this->deps["configMain"]->email;
        $message = \Swift_Message::newInstance()
        ->setSubject("ClamScan results for {$job->username}")
        ->setTo($job->reportAddress)
        ->setFrom([$mailConfig->from->address => $mailConfig->from->name])
        ->setBody($twig->render("email/scanResults.twig", ["job" => $reportJob]), "text/html");
        $this->deps["mailer"]->send($message);
    }
    
    /**
     * Magic construct function
     *
     * @param object $deps The pimple dependency container
     */
    public function __construct($deps)
    {
        $this->deps = $deps;
        parent::__construct();
    }
}
