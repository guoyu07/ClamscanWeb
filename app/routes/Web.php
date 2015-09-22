<?php
/**
 * This file contains the route definitions for the web interface for ClamScan
 * Web
 *
 * @license MIT
 */
$app["controllers.web.main"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Web\Main($app);
$app["controllers.web.pool"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Web\Pool($app);

/**
 * Get a new controller from the factory and make it so all routes aaccept
 * text/html.
 */
$web = $app["controllers_factory"];
$web->accept(["text/html"]);

/**
 * GET
 * The main index page
 */
$web->match("/", "controllers.web.main:renderIndex")
->method("GET")
->bind("webIndex");

/**
 * GET
 * This is here to help make correct URLs for files
 */
$web->match("files", function() {})
->method("GET")
->bind("files");

/**
 * GET
 * The pools index page
 */
$web->match("pools", "controllers.web.pool:renderIndex")
->method("GET");

/**
 *
 */
$web->match("pools/list", "controllers.web.pool:listPools")
->method("GET");

/**
 *
 */
$web->match("pools/get/{poolId}", "controllers.web.pool:getPool")
->method("GET");

/**
 * Mount the controller to the correct mount point
 */
$app->mount("/", $web);
