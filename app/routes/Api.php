<?php
/**
 * This file contains the route definitions for the REST-ful api for ClamScanWeb
 * @license MIT
 */
$app["controllers.api"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Api\Api($app);
$app["controllers.pool"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Api\Pool($app);
$app["controllers.server"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Api\Server($app);

/**
 * Get a new controller from the factory and set it so that all routes accept
 * application/vnd.collection+json
 */
$api = $app["controllers_factory"];
$api->accept(["application/vnd.collection+json"]);

/**
 * GET
 * Return the main billboard
 */
$api->match("/", "controllers.api:billboard")
->method("GET")
->bind("apiBillboard");

/**
 * GET
 * Return the billboard for pools
 */
$api->match("pools", "controllers.pool:billboard")
->method("GET")
->bind("poolsBillboard");

/**
 *
 */
$api->match("pools/create", "controllers.pool:createPoolTemplate")
->method("GET");

/**
 * PUT|POST
 * Create a new pool
 */
$api->match("pools/create", "controllers.pool:createPool")
->method("PUT|POST")
->bind("createPool");

/**
 * GET
 * Return a list of available pools
 */
$api->match("pools/list", "controllers.pool:returnPoolList")
->method("GET")
->bind("listPools");

/**
 * GET
 * Get a specific pool
 */
$api->match("pools/get/{poolId}", "controllers.pool:returnPool")
->method("GET")
->bind("getPool");

/**
 * PATCH|POST
 * Update a pool
 */
$api->match("pools/update/{poolId}", "controllers.pool:updatePool")
->method("PATCH|POST")
->bind("updatePool");

/**
 * DELETE|POST
 * Delete an existing pool
 */
$api->match("pools/delete/{poolId}", "controllers.pool:deletePool")
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
 *
 */
$api->match("servers/create", "controllers.server:createServerTemplate")
->method("GET");

/**
 * PUT|POST
 * Create a new server
 */
$api->match("servers/create", "controllers.server:createServer")
->method("PUT|POST")
->bind("createServer");

/**
 * GET
 * Get a list of available servers
 */
$api->match("servers/list", "controllers.server:returnServerList")
->method("GET")
->bind("listServers");

/**
 * GET
 * Get a specific server
 */
$api->match("servers/get/{serverId}", "controllers.server:returnServer")
->method("GET")
->bind("getServer");

/**
 * PATCH|POST
 * Update a specific server
 */
$api->match("servers/update/{serverId}", "controllers.server:updateServer")
->method("PATCH|POST")
->bind("updateServer");

/**
 * DELETE|POST
 * Delete an existing server
 */
$api->match("servers/delete/{serverId}", "controllers.server:deleteServer")
->method("DELETE|POST")
->bind("deleteServer");

/**
 * Mount the controller to the correct mount point
 */
$app->mount("/", $api);
