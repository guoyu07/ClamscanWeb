<?php
/**
 * This file contains the Pool controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers;

use Iu\Uits\Webtech\ClamScanWeb\Models\Pool as PoolModel;

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
     * @return object A Doctrine EagerCursor instance
     */
    public function getList()
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pools = $dm->createQueryBuilder("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->eagerCursor(true)
        ->getQuery()
        ->execute();
        return $pools;
    }
    
    /**
     * Get a specific pool by its id
     *
     * @param string $poolId The ID of the pool to get
     * @return object A pool model instance
     */
    public function get($poolId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $pool = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy(["id" => $poolId]);
        
        if (is_null($pool)) {
            throw new \RuntimeException("Not found", 404);
        }
        
        return $pool;
    }
    
    /**
     * Create a pool
     *
     * @param array The data required to make a new pool
     * @return object The pool instance which was just created
     */
    public function create($input)
    {
        
        /**
         * We need a name for this pool
         */
        if (!isset($input["name"])) {
            throw new \RuntimeException("Malformed request", 400);
        }
        
        /**
         * Having pools with duplicate names is undesirable
         */
        if ($this->exists("name", $input["name"])) {
            throw new \RuntimeException("Pool exists", 409);
        }
        
        /**
         * If we got good input and this won't be a duplicate pool, create the
         * requested pool
         */
        $pool = new PoolModel();
        $pool->setName($input["name"]);
        
        $dm = $this->app["deps"]["mongoDm"];
        $dm->persist($pool);
        $dm->flush();
        
        return $pool;
    }
    
    /**
     * Update a pool
     *
     * @param string $poolId The Id of the pool to update
     * @param array The data required to update a pool
     * @return object The pool instance which was updated
     */
    public function update($poolId, $input)
    {
        $pool = $this->get($poolId);
        
        /**
         * We were given a bad pool id to look up
         */
        if (is_null($pool)) {
            throw new \RuntimeException("Pool not found", 404);
        }
        
        /**
         * If we're unable to get the name data element from the request input
         * that means we were given a bad collection+json
         */
        if (!isset($input["name"])) {
            throw new \RuntimeException("Malformed request", 400);
        }
        
        /**
         * Having pools with duplicate names is undesirable
         */
        if ($this->exists("name", $input["name"])) {
            throw new \RuntimeException("Pool exists", 409);
        }
        
        /**
         * Set the name of the pool
         */
        $pool->setName($input["name"]);
        
        /**
         * Handle addition/removal of servers
         */
        //if ($servers = $this->getData("servers", $input)) {
        //    /** This has to wait for servers */
        //}
        
        /**
         * Persist the changes in the database
         */
        $this->app["deps"]["mongoDm"]->persist($pool);
        $this->app["deps"]["mongoDm"]->flush();
        
        /**
         * Return updated pool
         */
        return $pool;
    }
    
    /**
     * Delete a pool
     *
     * @param string $poolId The pool id
     * @return object The deleted pool
     */
    public function delete($poolId)
    {
        $pool = $this->get($poolId);
        
        /**
         * We were given a bad pool id to look up
         */
        if (is_null($pool)) {
            throw new \RuntimeException("Pool not found", 404);
        }
        
        /**
         * Remove the document and flush the document manager
         */
        $this->app["deps"]["mongoDm"]->remove($pool);
        $this->app["deps"]["mongoDm"]->flush();
        
        /**
         * Return the pool that was deleted from the database
         */
        return $pool;
    }
    
    /**
     * Check to see if a pool exists already with a field set to a specific
     * value
     *
     * @param string $field The name of the field
     * @param string $value The value of the field
     * @return bool Whether there's a pool with a field of the value specified
     */
    private function exists($field, $value)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $results = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Pool")
        ->findOneBy([$field => $value]);
        
        return (boolean)count($results);
    }
}
