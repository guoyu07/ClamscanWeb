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
class RunJobThreads extends Command
{
    /** @var An instance of the dependency container */
    private $deps;
    
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("jobs:runThreads")
        ->setDescription("Run a waiting job")
        ->addOption(
            "count",
            "c",
            InputOption::VALUE_REQUIRED,
            "The number of threads to run"
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobs = $this->getJobIdsFromDatabase();
        $jobCount = count($jobs);
        $jobsPerThread = ceil($jobCount / $input->getOption("count"));
        $arrays = array_chunk($jobs, $jobsPerThread);
        
        $commandName = __dir__ . "/../../../../../../commandAndControl jobs:run ";
        
        foreach($arrays as $array) {
            $jobsString = implode(" ", $this->processJobArray($array));
            
            $process = new Process($commandName . $jobsString);
            $process->start();
        }
        
    }
    
    /**
     * Get all waiting jobs from the database
     *
     * @return array An array of results from the database
     */
    private function getJobIdsFromDatabase()
    {
        $em = $this->deps["entityManager"];
        $query = $em->createQuery("SELECT j.id from Iu\Uits\Webtech\Clam\Model\Job j where j.state = 'waiting'");
        return $query->getResult();
    }
    
    /**
     * Process the raw jobs query into what is needed to run with
     *
     * @param array $input The raw jobs return
     * @return array The format we need to run
     */
    private function processJobArray($input)
    {
        $output = [];
        foreach ($input as $row) {
            $output[] = "-j {$row["id"]}";
        }
        return $output;
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
