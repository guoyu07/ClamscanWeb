<?php
/**
 * This file contains the Application class extension
 * @license MIT
 */
namespace Iu\Uits\Webtech;

/**
 * This just exists to include traits for the Silex Application class to use
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Application extends \Silex\Application
{
    use \Silex\Application\TwigTrait;
    use \Silex\Application\UrlGeneratorTrait;
}
