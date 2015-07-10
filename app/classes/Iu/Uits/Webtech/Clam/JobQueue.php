<?php
/**
 * This file contains the JobQueue controller for the ClamScanWeb application
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam;

use \Symfony\Component\HttpFoundation\Request;
use \Iu\Uits\Webtech\Clam\Model\Job;

/**
 * Job Queue Class
 * This class is the controller for the Job Queue.
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class JobQueue
{
    
    /** @var object The silex application instance */
    private $app;
    
    /**
     * The magic construct function
     *
     * @param object $app The silex application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * Add function
     * This function adds jobs to the job queue database
     *
     * @param object $request The symfony request object containing the post data
     * @return object A symfony response object
     */
    public function add(Request $request)
    {
        /**
         * Build the job object up
         */
        $job = new Job();
        $job->addedAt = new \DateTime("now", $this->app["deps"]["timezone"]);
        $job->addedBy = $request->server->get("REMOTE_USER");
        $job->state = "waiting";
        $job->username = $request->get("username");
        $job->reportAddress = $request->get("alertEmail");
        $job->excludeDirs = explode(",", $request->get("excludeDirs"));
        $job->excludeFiles = explode(",", $request->get("excludeFiles"));
        
        /**
         * Get an instance of the doctrine entity manager and use that to store
         * the job we just built
         */
        $em = $this->app["deps"]["entityManager"];
        $em->persist($job);
        $em->flush();
        
        return $this->app->redirect(
            $this->app["url_generator"]->generate("index")
        );
    }
    
    /**
     * Get a list of jobs by state
     *
     * @param array $state The list of states we want to get jobs in
     * @return object A symfony request object
     */
    public function getJobsByState($states)
    {
        $em = $this->app["deps"]["entityManager"];
        $qb = $em->createQueryBuilder();
        $qb->select("j")
        ->from("Iu\Uits\Webtech\Clam\Model\Job", "j");
        
        foreach ($states as $state) {
            $state = filter_var($state, FILTER_SANITIZE_STRING);
            $qb->orWhere("j.state = '{$state}'");
        }
        
        $query = $em->createQuery($qb->getDql());
        $jobs = $query->getArrayResult();
        return $jobs;
    }
}
