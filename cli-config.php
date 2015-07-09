<?php
/**
 * This file contains the cli config stuff to be able to run the doctrine schema
 * generation utility from the command line
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
use \Doctrine\ORM\Tools\Console\ConsoleRunner;

$app = require(__dir__ . "/bootstrap.php");

return ConsoleRunner::createHelperSet(
    $app["deps"]["entityManager"]
);
