<?php
/**
 * This file contains the Webtech Application class
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
namespace Iu\Uits\Webtech;

use Silex\Application\TwigTrait;
use Silex\Provider\UrlGeneratorServiceProvider;

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
