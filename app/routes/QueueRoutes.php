<?php
/**
 * This file contains the route definitions for management of the job queue
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
 */

/**
 * Add the JobQueue controller to the controllers for this application
 */
$app["controllers.queue"] = $app->share(function ($app) {
    $controller = new \Iu\Uits\Webtech\Clam\JobQueue($app);
    return $controller;
});

/**
 * Get a new controller from the factory
 */
$queueRoutes = $app["controllers_factory"];

/**
 * GET
 * Main index route
 */
$queueRoutes->post("/add", "controllers.queue:add")
->bind("addJobToQueue");

/**
 *
 */
$queueRoutes->get(
    "/jobs.json",
    function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
        $statuses = explode("|", $request->get("status"));
        return $app->json($app["controllers.queue"]->getJobsByState($statuses));
    })
->bind("jobsWaitingAndRunning");

/**
 * Mount the routes to the appropriate path
 */
$app->mount("/queue", $queueRoutes);
