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

        return $this->app->json($collection->toArray());
    }
    
    /**
     * Get a specific server by it's id
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
            return $this->app->json($collection->toArray());
        }
        
        $collection->addItem($this->createPoolItem($pool));
        return $this->app->json($collection->toArray());
    }
    
    /**
     * Create a pool
     *
     * @param object The symfony request object instance for this request
     * @return object A symfony json response object
     */
    public function create(Request $request)
    {
        $vars = json_decode($request->getContent());
        $collection = new Collection($this->url("createPool"));
        
        /**
         * Json decode returned null which probably means we were sent invalid
         * json input
         */
        if (is_null($vars)) {
            $collection->setError(new Collection\Error(
                "Malformed Request",
                400,
                "The json input was malformed"
            ));
            return $this->app->json($collection->toArray());
        }
        
        /**
         * Having pools with duplicate names is undesirable
         */
        if ($this->exists("name", $vars->name)) {
            $collection->setError(new Collection\Error(
                "Pool Exists",
                409,
                "A pool by that name already exists"
            ));
            return $this->app->json($collection->toArray());
        }
        
        /**
         * If we got good input and this won't be a duplicate pool, create the
         * requested pool
         */
        $pool = new PoolModel();
        $pool->setName($vars->name);
        
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
            return $this->app->json($collection->toArray());
        }
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
            return $this->app->json($collection->toArray());
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
}
