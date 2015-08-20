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
        ->setDescription("Run a waiting job")
        ->addOption(
            "job",
            "j",
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            "The id of the job to run"
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->deps["entityManager"];
        foreach ($input->getOption("job") as $id) {
            /**
             * Pick up the specified job, make sure it's current state is
             * "waiting", and mark it as running.
             */
            $job = $this->getJobFromDatabase($id);
            
            if ($job->state == "waiting") {
                $job->state = "running";
                $em->persist($job);
                $em->flush();
                
                /**
                 * Run the job and use the output to build a result object
                 * to store in the database
                 */
                $commandOutput = $this->runCommand($job, $output);
                $result = $this->buildResultObject(
                    $this->parseOutput($commandOutput)
                );
                
                /**
                 * Link the result to our job and store both in the database
                 */
                $job->result = $result->id;
                $job->state = "finished";
                $em->persist($job);
                $em->persist($result);
                $em->flush();
                
                /**
                 * Send an email report if the job was manually scheduled and has an
                 * address to send the report in the job
                 */
                if (!$job->massScheduled && $job->reportAddress != "") {
                    $this->sendEmailReport($job, $result);
                }
            } else {
                if (!$output->isQuiet()) {
                    $output->writeln("<error>Job {$id} is either already running or finished</error>");
                }
            }
        }
    }
    
    /**
     * Fetch a specific job from the database
     *
     * @param string $id The id of the job to fetch from the database
     * @return object The requested job object
     */
    private function getJobFromDatabase($id)
    {
        $em = $this->deps["entityManager"];
        $query = $em->createQuery("SELECT j from Iu\Uits\Webtech\Clam\Model\Job j where j.id = :id");
        $query->setParameters(["id" => $id]);
        $job = $query->getSingleResult();
        return $job;
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
     * Run the command for the job
     *
     * @param object $job The job object
     * @param object $output The output interface from the execute function
     * @return string The output from the command
     */
    private function runCommand($job, $output)
    {
        $process = new Process($this->buildJobCommand($job));
        $process->setTimeout(36000);
        
        if ($output->isDebug()) {
            $output->writeln("<info>{$process->getCommandLine()}</info>");
        }
        
        $process->start();
        
        if ($output->isDebug()) {
            $process->wait(function ($type, $message) use ($output) {
                if ($type == "err") {
                    $message = "<error>{$message}</error>";
                }
                $output->writeln($message);
            });
        } else {
            $process->wait();
        }
        
        return $process->getOutput();
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
     * Build a result object out of the results array produced by the
     * parseOutput function
     *
     * @param array $results The results array
     * @return object The result object containing the values from the input
     */
    private function buildResultObject($results)
    {
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
        return $result;
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
