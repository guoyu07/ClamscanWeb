<?php
/**
 * This file contains the main web controller for the ClamScanWeb application
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam\Web;

use Iu\Uits\Webtech\Clam\JobQueue;
use \Symfony\Component\HttpFoundation\Request;

/**
 * Main Controller Class
 * This class is the main web page controller for this application
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class MainController
{
    /** @var object The silex application */
    private $app;
    
    /**
     * Magic Construct Function
     *
     * @param object $app The silex application itself
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * This function will display the form to add a new job to the queue
     *
     * @return object A symfony response object containing the rendered template
     */
    public function showEnqueuePage()
    {
        $queue = new JobQueue($this->app);
        $waitingAndRunning = $queue->getJobsByState(["waiting", "running"]);
        
        $options = $this->app["options"];
        $options["waitAndRunJobs"] = $waitingAndRunning;
        
        return $this->app["twig"]->render(
            "pages/index.twig",
            $options
        );
    }
    
    /**
     *
     */
    public function listAllJobs()
    {
        $queue = new JobQueue($this->app);
        $jobs = $queue->getJobsByState(["waiting", "running", "failed", "finished"]);
        
        $options = $this->app["options"];
        $options["jobs"] = $jobs;
        
        return $this->app["twig"]->render(
            "pages/allJobsList.twig",
            $options
        );
    }
}
