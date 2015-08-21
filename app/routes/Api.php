<?php
/**
 * This file contains the route definitions for the REST-ful api for ClamScanWeb
 * @license MIT
 */
$app["controllers.pool"] = new \Iu\Uits\Webtech\ClamScanWeb\Controllers\Pool($app);
$api = $app["controllers_factory"];

/**
 * GET
 * Return a list of available pools
 */
$api->get("pools/list", "controllers.pool:getList")
->bind("listPools");

/**
 * GET
 * Get a specific pool
 */
$api->get("pools/get/{poolId}", "controllers.pool:get")
->bind("getPool");

/**
 * PUT|POST
 * Create a new pool
 */
$api->match("pools/create", "controllers.pool:create")
->method("PUT|POST")
->bind("createPool");

/**
 * DELETE|POST
 * Delete an existing pool
 */
$api->match("pools/delete/{poolId}", "controllers.pool:delete")
->method("DELETE|POST")
->bind("deletePool");

$app->mount("/api", $api);
