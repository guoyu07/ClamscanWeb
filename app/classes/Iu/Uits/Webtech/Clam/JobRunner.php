<?php
/**
 *
 */
namespace Iu\Uits\Webtech\Clam;

use Iu\Uits\Webtech\Clam\Model\Job;
use Iu\Uits\Webtech\Clam\Model\Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

/**
 *
 */
class JobRunner extends Command
{
    /** @var An instance of the dependency container */
    private $deps;
    
    /**
     *
     */
    protected function configure()
    {
        $this->setName("jobs:run");
    }
    
    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelperSet()->get("formatter");
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
            
            $directory = $this->deps["configMain"]->command->paths->homeRoot;
            
            /**
             * Run the actual clam scan
             */
            $process = new Process("clamscan --recursive=yes {$directory}");
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
            $em->flush();
        }
    }
    
    /**
     *
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
     *
     */
    public function __construct($deps)
    {
        $this->deps = $deps;
        parent::__construct();
    }
}
