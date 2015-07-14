<?php
/**
 * This file contains the Webtech Application class
 * @license MIT
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
namespace Iu\Uits\Webtech;

/**
 * This class extends the Silex Application class to add in a couple traits
 * which are too useful to ignore.
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Application extends \Silex\Application
{
    use \Silex\Application\TwigTrait;
    use \Silex\Application\UrlGeneratorTrait;
    
    /**
     * The magic construct function
     */
    public function __construct()
    {
        parent::__construct();
    }
}
