<?php
/**
 * This file contains the main web controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Web;

use Symfony\Component\HttpFoundation\Request;

/**
 * This class provides functionality for the main page of the site
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Main
{
    /** Use the WebHelper trait */
    use \Iu\Uits\Webtech\ClamScanWeb\Traits\WebHelper;
    
    /**
     * Magic constructor function
     *
     * @param object $app The application instance
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * Return the index page
     *
     * @param object $request A symfony request object
     * @return object A symfony response object
     */
    public function renderIndex(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        return $this->app->render("pages/index.twig", $variables);
    }
}

