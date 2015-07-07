<?php

/**
 * Include the bootstrap file for this application
 */
$app = require(__dir__ . "/../bootstrap.php");

/**
 * Dynamically load any route definitions
 */
foreach (glob(__dir__ . "/../app/routes/*.php") as $routeFile) {
    require($routeFile);
}

/**
 * Finally, run the application to deliver the requested page
 */
$app->run();
