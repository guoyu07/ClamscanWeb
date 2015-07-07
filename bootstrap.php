<?php

/**
 * The composer autoloader is the glue that holds this whole thing together
 */
require(__dir__ . "/vendor/autoload.php");

/**
 * This application also uses a dependency container. We load that here
 */
$deps = require(__dir__ . "/app/resources/dependencyContainer.php");

/**
 * Define the namespaces we'll be using so we don't have to resolve the full
 * namespace every time we use one.
 */
use Iu\Uits\Webtech\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;

/**
 * Instantiate silex application
 */
$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

/**
 * Add the dependency container to the application
 */
$app["deps"] = $deps;

/**
 * Add the site options to the app as a flat array for easier access
 */
$app["options"] = (array)$app["deps"]["configMain"]->site->interface;

/**
 * Instantiate the twig service provider for silex
 */
$app->register(new TwigServiceProvider(), [
    "twig.path" => __dir__ . "/app/view/",
    "twig.options" => [
        "mode" => $app["deps"]["configMain"]->site->settings->mode,
        "debug" => $app["deps"]["configMain"]->site->settings->debug,
        "cache" => $app["deps"]["configMain"]->site->settings->twigCache
    ],
]);

/**
 * If in debug mode, use whoops to display error messages and stack traces
 */
if ($app["deps"]["configMain"]->site->settings->debug) {
    $app->register(new WhoopsServiceProvider);
}

/**
 * Return the application object to the script including this file
 */
return $app;
