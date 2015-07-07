<?php
/**
 * This file contains the Webtech Application class
 * @license MIT
 */
namespace Iu\Uits\Webtech;

use Symfony\Application\TwigTrait;
use Symfony\Application\UrlGeneratorTrait;

/**
 * This class extends the Silex Application class to add in a couple traits
 * which are too useful to ignore.
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Application extends \Silex\Application
{
    /**
     * The magic construct function
     */
    public function __construct()
    {
        parent::__construct();
    }
}
