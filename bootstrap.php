<?php
require __dir__ . "/vendor/autoload.php";
require __dir__ . "/app/resources/dependencyContainer.php";

$app = new Iu\Uits\Webtech\Application();
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new WhoopsSilex\WhoopsServiceProvider());

/**
 *
 */
$app["deps"] = $deps;

return $app;
