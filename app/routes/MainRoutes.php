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
 * Mount the routes to the appropriate path
 */
$app->mount("/", $mainRoutes);
