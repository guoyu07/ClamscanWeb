<?php
$app = require __dir__ . "/../bootstrap.php";

foreach (glob(__dir__ . "/../app/routes/*.php") as $routeFile) {
    require ($routeFile);
}

$app->get("/", function () {
    return new Symfony\Component\HttpFoundation\Response("Hi!");
});

$app->run();
