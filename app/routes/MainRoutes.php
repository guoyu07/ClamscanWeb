<?php
/**
 * This file contains the route definitions for the web display
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
 */

/**
 * Add the MainController class to the controllers for this application
 */
$app["controllers.main"] = $app->share(function ($app) {
    $controller = new \Iu\Uits\Webtech\Clam\Web\MainController($app);
    return $controller;
});

/**
 * Get a new controller from the factory
 */
$mainRoutes = $app["controllers_factory"];

/**
 * GET
 * Main index route
 */
$mainRoutes->get("/", "controllers.main:showEnqueuePage")
->bind("index");

/**
 * GET
 * All jobs route
 */
$mainRoutes->get("/list", "controllers.main:listAllJobs")
->bind("listAllJobs");

/**
 *
 */
$mainRoutes->get("/listMassScheduledJobs", "controllers.main:listMassScheduledJobs")
->bind("listMassScheduledJobs");

/**
 * GET
 * Single job details route
 */
$mainRoutes->get("/job/{jobid}", "controllers.main:getJob")
->bind("getJob");

/**
 * GET
 * This route just exists so that it's possible to use the url generator to
 * generate urls for external resources such as javascript and css files
 */
$mainRoutes->get("/files", function() {})
->bind("files");

/**
 * Mount the routes to the appropriate path
 */
$app->mount("/", $mainRoutes);
