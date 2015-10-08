<?php
/**
 * This file contains the route definitions for the web interface for ClamScan
 * Web
 *
 * @license MIT
 */
$app["controllers.web.main"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Web\Main($app);
$app["controllers.web.pool"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Web\Pool($app);
$app["controllers.web.server"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Web\Server($app);

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
 * GET
 * The form to fill out to create a new pool
 */
$web->match("pools/create", "controllers.web.pool:getCreatePage")
->method("GET");

/**
 * GET
 * The pools list page
 */
$web->match("pools/list", "controllers.web.pool:listPools")
->method("GET");

/**
 * GET
 * A specific pool
 */
$web->match("pools/get/{poolId}", "controllers.web.pool:getPool")
->method("GET");

/**
 *
 */
$web->match("servers", "controllers.web.server:renderIndex")
->method("GET");

/**
 *
 */
$web->match("servers/create", "controllers.web.server:getCreatePage")
->method("GET");

/**
 *
 */
$web->match("servers/create", "controllers.web.server:createServer")
->method("POST");

/**
 *
 */
$web->match("servers/list", "controllers.web.server:listServers")
->method("GET");

/**
 *
 */
$web->match("servers/get/{serverId}", "controllers.web.server:getServer")
->method("GET");

/**
 *
 */
$web->match("servers/update/{serverId}", "controllers.web.server:getUpdatePage")
->method("GET");

/**
 *
 */
$web->match("servers/update/{serverId}", "controllers.web.server:updateServer")
->method("POST");

/**
 *
 */
$web->match("servers/delete/{serverId}", "controllers.web.server:deleteServer")
->method("GET");

/**
 * Mount the controller to the correct mount point
 */
$app->mount("/", $web);
