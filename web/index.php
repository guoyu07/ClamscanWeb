<?php
$app = require __dir__ . "/../bootstrap.php";

foreach (glob(__dir__ . "/../app/routes/*.php") as $routeFile) {
    require ($routeFile);
}

$app->run();
