<?php
/**
 * This file contains the route definitions for the REST-ful api for ClamScanWeb
 * @license MIT
 */
$app["controllers.pool"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Pool($app);
$app["controllers.server"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Server($app);
$api = $app["controllers_factory"];

/**
 * PUT|POST
 * Create a new pool
 */
$api->match("pools/create", "controllers.pool:create")
->method("PUT|POST")
->bind("createPool");

/**
 * GET
 * Return a list of available pools
 */
$api->match("pools/list", "controllers.pool:getList")
->method("GET")
->bind("listPools");

/**
 * GET
 * Get a specific pool
 */
$api->match("pools/get/{poolId}", "controllers.pool:get")
->method("GET")
->bind("getPool");

/**
 * PATCH|POST
 * Update a pool
 */
$api->match("pools/update/{poolId}", "controllers.pool:update")
->method("PATCH|POST")
->bind("updatePool");

/**
 * DELETE|POST
 * Delete an existing pool
 */
$api->match("pools/delete/{poolId}", "controllers.pool:delete")
->method("DELETE|POST")
->bind("deletePool");

/**
 * GET
 * Get the billboard for servers
 */
$api->match("servers", "controllers.server:billboard")
->method("GET")
->bind("serversBillboard");

/**
 * PUT|POST
 * Create a new server
 */
$api->match("servers/create", "controllers.server:create")
->method("PUT|POST")
->bind("createServer");

/**
 * GET
 * Get a list of available servers
 */
$api->match("servers/list", "controllers.server:getList")
->method("GET")
->bind("listServers");

/**
 * GET
 * Get a specific server
 */
$api->match("servers/get/{serverId}", "controllers.server:get")
->method("GET")
->bind("getServer");

/**
 * PATCH|POST
 */
$api->match("servers/update/{serverId}", "controllers.server:update")
->method("PATCH|POST")
->bind("updateServer");

/**
 * DELETE|POST
 * Delete an existing server
 */
$api->match("servers/delete/{serverId}", "controllers.server:delete")
->method("DELETE|POST")
->bind("deleteServer");

$app->mount("/api", $api);
