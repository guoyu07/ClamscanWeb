<?php
/**
 * This file contains the Api api controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Api;

use CollectionJson\Collection;
use CollectionJson\Property;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class provides functionality specific to the api itself
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Api
{
    /** Use the CollectionHelper trait */
    use \Iu\Uits\Webtech\ClamScanWeb\Traits\CollectionHelper;
    
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
     * Return the api billboard
     *
     * @return object A symfony response instance
     */
    public function billboard()
    {
        $collection = new Collection($this->url("apiBillboard"));
        $collection->addLink($this->poolsBillboardLink());
        $collection->addLink($this->serversBillboardLink());
        
        return $this->outputCollection($collection);
    }
    
    /**
     * Get a link object for the pools billboard
     *
     * @return object A link object
     */
    private function poolsBillboardLink()
    {
        return new Property\Link(
            $this->url("poolsBillboard"),
            "pools",
            "pools",
            null,
            "Pool Management"
        );
    }
    
    /**
     * Get a link object for the servers billboard
     *
     * @return object A link object
     */
    private function serversBillboardLink()
    {
        return new Property\Link(
            $this->url("serversBillboard"),
            "servers",
            "servers",
            null,
            "Server Management"
        );
    }
}
