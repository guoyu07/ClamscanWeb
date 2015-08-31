<?php
/**
 * This file contains the Pool controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Iu\Uits\Webtech\ClamScanWeb\Models\Pool as PoolModel;
use CollectionJson\Collection;
use CollectionJson\Property;

/**
 * Pool controller class
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Pool
{
    
    /** @var object The application object */
    private $app;
    
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
     * Get all the pools
     *
     * @return object A symfony json response object
     */
    public function getList()
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pools = $dm->createQueryBuilder("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->eagerCursor(true)
        ->getQuery()
        ->execute();
        
        $collection = new Collection($this->url("listPools"));

        foreach ($pools as $pool) {
            $collection->addItem($this->createPoolItem($pool));
        }

        return $this->app->json(
            $collection->toArray(),
            200,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * Get a specific pool by its id
     *
     * @param string $poolId The ID of the pool to get
     * @return object A symfony response object
     */
    public function get($poolId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pool = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy(["id" => $poolId]);
        
        $collection = new Collection($this->url("getPool", ["poolId" => $poolId]));
        
        /**
         * We were given a bad pool id to look up
         */
        if (is_null($pool)) {
            $collection->setError(new Collection\Error(
                "Unknown Pool Id",
                404,
                "The given pool was not found"
            ));
            return $this->app->json(
                $collection->toArray(),
                404,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        $collection->addItem($this->createPoolItem($pool));
        return $this->app->json(
            $collection->toArray(),
            200,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * Create a pool
     *
     * @param object The symfony request object instance for this request
     * @return object A symfony json response object
     */
    public function create(Request $request)
    {
        $input = json_decode($request->getContent());
        
        /**
         * If we're unable to get the name data element from the request input
         * that means we were given a bad collection+json
         */
        if (!$this->getData("name", $input)) {
            $collection = new Collection($this->url("createPool"));
            $collection->setError(new Collection\Error(
                "Malformed Request",
                400,
                "The json input was malformed"
            ));
            return $this->app->json(
                $collection->toArray(),
                400,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        $name = $this->getData("name", $input)["value"];
        
        /**
         * Having pools with duplicate names is undesirable
         */
        if ($this->exists("name", $name)) {
            $collection = new Collection($this->url("createPool"));
            $collection->setError(new Collection\Error(
                "Pool Exists",
                409,
                "A pool by that name already exists"
            ));
            return $this->app->json(
                $collection->toArray(),
                409,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        /**
         * If we got good input and this won't be a duplicate pool, create the
         * requested pool
         */
        $pool = new PoolModel();
        $pool->setName($name);
        
        $dm = $this->app["deps"]["mongoDm"];
        $dm->persist($pool);
        $dm->flush();
        
        /**
         * Now that the pool's been created, we should return the proper
         * response about it
         */
        $poolUrl = $this->url("getPool", ["poolId" => $pool->getId()]);
        $response = new Response(
            "Created",
            201,
            ["Location" => $poolUrl]
        );
        return $response;
    }
    
    /**
     * Update a pool
     *
     * @param string $poolId The Id of the pool to update
     * @param object The symfony request object
     * @return object A symfony response object
     */
    public function update($poolId, Request $request)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pool = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy(["id" => $poolId]);
        $input = json_decode($request->getContent());
        
        /**
         * We were given a bad pool id to look up
         */
        if (is_null($pool)) {
            $collection = new Collection($this->url("deletePool", ["poolId" => $poolId]));
            $collection->setError(new Collection\Error(
                "Unknown Pool Id",
                404,
                "The given pool was not found"
            ));
            return $this->app->json(
                $collection->toArray(),
                404,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        /**
         * If we're unable to get the name data element from the request input
         * that means we were given a bad collection+json
         */
        if (!$this->getData("name", $input)) {
            $collection = new Collection($this->url("createPool"));
            $collection->setError(new Collection\Error(
                "Malformed Request",
                400,
                "The json input was malformed"
            ));
            return $this->app->json(
                $collection->toArray(),
                400,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        $name = $this->getData("name", $input)["value"];
        
        /**
         * Having pools with duplicate names is undesirable
         */
        if ($this->exists("name", $name)) {
            $collection = new Collection($this->url("createPool"));
            $collection->setError(new Collection\Error(
                "Pool Exists",
                409,
                "A pool by that name already exists"
            ));
            return $this->app->json(
                $collection->toArray(),
                409,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        /**
         * Set the name of the pool
         */
        $pool->setName($name);
        
        /**
         * Handle addition/removal of servers
         */
        //if ($servers = $this->getData("servers", $input)) {
        //    /** This has to wait for servers */
        //}
        
        $dm->persist($pool);
        $dm->flush();
        
        /**
         * Send the response back out
         */
        $response = new Response(
            "",
            200
        );
        return $response;
    }
    
    /**
     * Delete a pool
     *
     * @param string $poolId The pool id
     * @return object A symfony response object
     */
    public function delete($poolId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pool = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy(["id" => $poolId]);
        
        /**
         * We were given a bad pool id to look up
         */
        if (is_null($pool)) {
            $collection = new Collection($this->url("deletePool", ["poolId" => $poolId]));
            $collection->setError(new Collection\Error(
                "Unknown Pool Id",
                404,
                "The given pool was not found"
            ));
            return $this->app->json(
                $collection->toArray(),
                404,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        /**
         * Remove the document and flush the document manager
         */
        $dm->remove($pool);
        $dm->flush();
        
        /**
         * Now that the pool is deleted, we send the response about what
         * happened
         */
        $response = new Response("", 204);
        return $response;
    }
    
    /**
     * Check to see if a pool exists already with a field set to a specific
     * value
     *
     * @param string $field The name of the field
     * @param string $value The value of the field
     */
    private function exists($field, $value)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $results = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy([$field => $value]);
        
        return (boolean)count($results);
    }
    
    /**
     * The symfony url generator is nice and very convenient, but it's also
     * sometimes a bit long-winded to use
     *
     * @param string $route The name of the route to generate a url for
     * @param array $params The array of route parameters (default: empty array)
     * @return string The url requested
     */
    private function url($route, $params = [])
    {
        return $this->app["url_generator"]->generate(
            $route,
            $params,
            UrlGenerator::ABSOLUTE_URL
        );
    }
    
    /**
     * Because there are several times where we need to create items out of pool
     * classes, it's better to have a function to handle that.
     *
     * @param object $pool The pool class
     * @return object A collection item instance
     */
    private function createPoolItem(PoolModel $pool)
    {
        $item = new Collection\Item(
            $this->url("getPool", ["poolId" => $pool->getId()])
        );
        
        foreach ($pool->toArray() as $key => $value) {
            $data = new Property\Data($key, $value);
            $item->addData($data);
        }
        
        return $item;
    }
    
    /**
     * This function gets data from the data array of the submission
     *
     * @param string $name The name of the data to get
     * @param mixed $input The collection from which to get the data
     * @return mixed The requested data or false on failure
     */
    private function getData($name, $input)
    {
        /**
         * Since we're using array_ functions, the data we're using needs to be an
         * array too
         */
        if (is_object($input)) {
            $input = json_decode(json_encode($input), true);
        }
        
        /**
         * The data was formatted incorrectly when it was sent in or didn't
         * include the right stuff
         */
        if (!isset($input["template"]["data"])) {
            return false;
        }
        
       $dataItems = $input["template"]["data"];
       $key = array_search($name, array_column($dataItems, "name"));
       
       if ($key !== false) {
           return $dataItems[$key];
       }
       
       return false;
    }
}
