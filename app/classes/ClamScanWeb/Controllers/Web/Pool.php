<?php
/**
 * This file contains the pools web controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Web;

use Symfony\Component\HttpFoundation\Request;

/**
 * This class provides functionality for the pools parts of the website
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Pool
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
     *
     */
    public function renderIndex(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        //return $this->app->json($reply);
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        //var_dump($variables);die();
        
        return $this->app->render("pages/pools.twig", $variables);
    }
    
    /**
     *
     */
    public function listPools(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        //var_dump($response->getContent());die();
        
        //return $this->app->json($reply);
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        return $this->app->render("pages/poolsView.twig", $variables);
    }
    
    /**
     *
     */
    public function getPool(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        //return $this->app->json($reply);
        return $this->app->render("pages/pool.twig", $variables);
    }
}
