<?php

$web = $app["controllers_factory"];

$web->get("/", function ($who) use ($app) {
    $subRequest = \Symfony\Component\HttpFoundation\Request::create("/api/");
    $response = json_decode($app->handle($subRequest)->getContent());
    
    return new \Symfony\Component\HttpFoundation\Response($response->message);
})->value("who", "Unknown User");

$app->mount("/", $web);
