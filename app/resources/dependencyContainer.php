<?php

use Pimple\Container;

$deps = new Container();

/**
 * Define the config function
 *
 * @return object The config object
 */
$deps["config"] = function () {
    $config = file_get_contents(__dir__ . "/../../config/main.json");
    return json_decode($config);
};

/**
 * Define the mongoClient function
 *
 * @param object $c The pimple container
 * @return object The connection to the mongodb server
 */
$deps["mongoClient"] = function ($c) {
    $servers = "mongodb://" . implode(",", $c["config"]->mongodb->server);
    
    $options = [];
    $options["connectTimeoutMS"] = $c["config"]->mongodb->connectTimeoutMS;
    $options["socketTimeoutMS"] = $c["config"]->mongodb->socketTimeoutMS;
    
    if (strlen($c["config"]->mongodb->replicaSet) > 0) {
        $options["replicaSet"] = $c["config"]->mongodb->replicaSet;
    }
    
    $client = new \MongoClient($servers, $options);
    $client->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED);
    
    return $client;
};

/**
 * Define the mongoDb function
 *
 * @param object $c The pimple container
 * @return object The mongodb object
 */
$deps["mongoDb"] = function ($c) {
    $client = $c["mongoClient"];
    return $client->selectDB($c["config"]->mongodb->database);
};

/**
 * Define the mongoDm function
 *
 * @param object $c The pimple container
 * @return object The instance of DocumentManager for your database
 */
$deps["mongoDm"] = function ($c) {
    $connection = new Doctrine\MongoDB\Connection($c["mongoClient"]);
    $config = new Doctrine\ODM\MongoDB\Configuration();
    $config->setProxyDir(__dir__ . "/../../cache/proxies");
    $config->setProxyNamespace("Proxies");
    $config->setHydratorDir(__dir__ . "/../../cache/hydrators");
    $config->setHydratorNamespace("Hydrators");
    $config->setDefaultDB($c["config"]->mongodb->database);
    $config->setMetadataDriverImpl(
        Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::create(__dir__ . "/app/models")
    );
    Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
    
    return Doctrine\ODM\MongoDB\DocumentManager::create($connection, $config);
};
