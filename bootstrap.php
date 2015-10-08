<?php
require __dir__ . "/vendor/autoload.php";
require __dir__ . "/app/resources/dependencyContainer.php";

$app = new Iu\Uits\Webtech\Application();
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new WhoopsSilex\WhoopsServiceProvider());
$app->register(new Breaker1\Silex\AcceptHeaderServiceProvider\ServiceProvider());

/**
 * Store the dependency container in the application
 */
$app["deps"] = $deps;

/**
 * Set the application debug mode
 * Among other things, this will cause the twig service provider to add the
 * twig debug extension to twig to enable the dump function
 */
$app["debug"] = $app["deps"]["config"]->application->debug;

/**
 * Initialize the twig service provider
 */
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    [
        "twig.path" => __dir__ . "/app/views",
        "twig.options" => [
            "mode" => $app["deps"]["config"]->application->mode,
            "debug" => $app["deps"]["config"]->application->debug,
            "cache" => $app["deps"]["config"]->web->templateCache
        ]
    ]
);

return $app;
