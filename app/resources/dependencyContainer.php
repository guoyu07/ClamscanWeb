<?php
/**
 * This file contains the dependency container for this application.
 * @license MIT
 */

/**
 * Instantiate the pimple container
 */
$deps = new Pimple();

/**
 * Define the main config container
 * 
 * @return object Returns a json object representing the main config
 */
$deps["configMain"] = $deps->share(function () {
    $configFile = __dir__ . "/../config/main.json";
    
    if (!file_exists($configFile)) {
        throw new \RuntimeException("File not found: {$configFile}", 404);
    }
    
    if (!is_readable($configFile)) {
        throw new \RuntimeException("Found file, but can't read it: {$configFile}", 403);
    }
    
    $contents = file_get_contents($configFile);
    return json_decode($contents);
});

/**
 * Define the database container
 * 
 * @return object An instance of a doctrine connection
 */
$deps["database"] = $deps->share(function ($deps) {
    $params = (array)$deps["configMain"]->database;
    $config = new \Doctrine\DBAL\Configuration();
    $database = \Doctrine\DBAL\DriverManager::getConnection(
        $params,
        $config
    );
    return $database;
});

return $deps;
