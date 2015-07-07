<?php
/**
 *
 */

/**
 *
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
 *
 */
$app->mount("/", $mainRoutes);
