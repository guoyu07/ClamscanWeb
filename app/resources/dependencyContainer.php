<?php
/**
 * This file contains the dependency container for this application.
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
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

/**
 * Define the entity manager container
 *
 * @return object An instance of the doctrine orm entity manager
 */
$deps["entityManager"] = $deps->share(function ($deps) {
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        [__dir__ . "/../classes"],
        $deps["configMain"]->site->settings->debug
    );
    return \Doctrine\ORM\EntityManager::create(
        (array)$deps["configMain"]->database,
        $config
    );
});

/**
 * Define the application timezone container
 *
 * @return object An instance of DateTimeZone with the configured timezone set
 */
$deps["timezone"] = $deps->share(function ($deps) {
    return new \DateTimeZone($deps["configMain"]->site->settings->timezone);
});


/**
 * Define the mail transport container
 *
 * @return object An instance of Swift's Transport configured to use
 */
$deps["mailTransport"] = $deps->share(function ($deps) {
    $mailConfig = $deps["configMain"]->email;
    $transport = Swift_SmtpTransport::newInstance()
    ->setHost($mailConfig->host)
    ->setPort($mailConfig->port)
    ->setUsername($mailConfig->username)
    ->setPassword($mailConfig->password)
    ->setEncryption($mailConfig->encryption);
    
    return $transport;
});

/**
 * Define the mailer container
 *
 * @return object An instance of Switft's Mailer class set to use the transport
 */
$deps["mailer"] = $deps->share(function ($deps) {
    return Swift_Mailer::newInstance($deps["mailTransport"]);
});

/**
 * Return the dependency container
 */
return $deps;
