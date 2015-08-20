<?php
/**
 * This file contains the Job Runner command class
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam;

use Iu\Uits\Webtech\Clam\Model\Job;
use Breaker1\Passwd\Passwd;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleGroupScan extends Command
{
    
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("queue:scheduleGroupScan")
        ->setDescription("Schedule a scan for all members of a given group")
        ->addOption(
            "group",
            "g",
            InputOption::VALUE_REQUIRED,
            "Schedule a scan for all members of a given group"
        )
        ->addOption(
            "min-uid",
            "u",
            InputOption::VALUE_OPTIONAL,
            "Only schedule a scan for users above this id",
            0
        )
        ->addOption(
            "exclude-user",
            "x",
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            "Exclude a user from being scheduled",
            []
        )
        ->addOption(
            "exclude-file",
            "f",
            InputOption::VALUE_OPTIONAL,
            "Regex for file exclusion (applies to all jobs)",
            ""
        )
        ->addOption(
            "exclude-dir",
            "d",
            InputOption::VALUE_OPTIONAL,
            "Regex for directory exclusion (applies to all jobs)",
            ""
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $passwd = new Passwd();
        $group = $input->getOption("group");
        $minUid = $input->getOption("min-uid");
        $excludedUsers = $input->getOption("exclude-user");
        $excludeFile = [$input->getOption("exclude-file")];
        $excludeDir = [$input->getOption("exclude-dir")];
        
        foreach($passwd->getUsersByGroup($group) as $user) {
            if ($user["uid"] >= $minUid && !in_array($user["username"], $excludedUsers)) {
                $job = new Job();
                $job->addedAt = new \DateTime("now", $this->deps["timezone"]);
                $job->addedBy = "schedule";
                $job->state = "waiting";
                $job->username = $user["username"];
                $job->reportAddress = "";
                $job->excludeDirs = $excludeDir;
                $job->excludeFiles = $excludeFile;
                $job->massScheduled = true;
                $this->deps["entityManager"]->persist($job);
            }
        }
        $this->deps["entityManager"]->flush();
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
