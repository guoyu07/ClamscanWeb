<?php
/**
 * This file contains the WebHelper trait used in the Clamscan Web application
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * This trait provides some useful helper functions for dealing with things the
 * web front end needs to do
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
trait WebHelper
{
    /**
     * Make a sub-request to the api route
     *
     * @param object $request A symfony request object for the front end
     * @return object A symfony response object from the api
     */
    private function makeSubquery(Request $request)
    {
        /**
         * We need to preserve the method for later after we nullify everything
         */
        $method = $request->getMethod();
        
        /**
         * By the time we're here, the framework has already worked out which
         * controller to use for this request, we unset that crap all here so
         * that it's re-routed.
         */
        $request->attributes->replace([]);
        
        /**
         * Unset the given Accept and HTTP_ACCEPT headers, they'll (probably) be
         * wrong for what we're doing here.
         */
        $request->headers->replace(
            ["Accept" => "application/vnd.collection+json"]
        );
        $request->server->replace(
            ["HTTP_ACCEPT" => "application/vnd.collection+json"]
        );
        
        /**
         * Re-set the method
         */
        $request->setMethod($method);
        
        //var_dump($request);die();
        
        /**
         * Finally return the response from the application for the modified
         * request.
         */
        return $this->app->handle($request);
    }
    
    /**
     * Return the basic template variables
     *
     * @return array The basic template variables
     */
    private function templateVars()
    {
        return [
            "output" => $this->app["deps"]["config"]->web->interface->output,
            "layout" => $this->app["deps"]["config"]->web->interface->layout,
        ];
    }
}
