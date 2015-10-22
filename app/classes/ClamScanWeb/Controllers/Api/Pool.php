<?php
/**
 * This file contains the Pool api controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Api;

use Breaker1\CollectionJson\Property\Data;
use Breaker1\CollectionJson\Property\Validation;
use CollectionJson\Collection;
use CollectionJson\Property;
use Iu\Uits\Webtech\ClamScanWeb\Controllers\Pool as PoolController;
use Iu\Uits\Webtech\ClamScanWeb\Models\Pool as PoolModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class provides collection+json functionality for the Pool controller
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Pool extends PoolController
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
        parent::__construct($app);
    }
    
    /**
     * Return the billboard for the pools
     *
     * @return object A symfony response object
     */
    public function billboard()
    {
        $collection = new Collection(
            $this->url("poolsBillboard")
        );
        $collection->addLink($this->homeLink());
        $collection->addLink($this->listPoolsLink());
        $collection->addLink($this->getPoolLink());
        $collection->addQuery($this->createPoolQuery());
        $collection->addQuery($this->updatePoolQuery());
        $collection->addQuery($this->deletePoolQuery());
        return $this->outputCollection($collection);
    }
    
    /**
     *
     */
    public function createPoolTemplate(Request $request)
    {
        $collection = new Collection($this->url("createPool"));
        $collection->addLink($this->homeLink());
        $collection->addLink($this->listPoolsLink());
        $collection->addLink($this->getPoolLink());
        $collection->addQuery($this->createPoolQuery());
        return $this->outputCollection($collection);
    }
    
    /**
     * Create a new pool
     *
     * @param object A symfony request object instance
     * @return object A symfony response object instance
     */
    public function createPool(Request $request)
    {
        /**
         * Get the json input from the request object and decode it to a useful
         * array
         */
        $input = json_decode($request->getContent());
        $input = $this->dataToArray($input->template->data);
        
        /**
         * Attempt to create the pool with the given input
         */
        try {
            $pool = $this->create($input);
            
            /**
             * If everything went right, we should send the correct response
             */
            $poolUrl = $this->url("getPool", ["poolId" => $pool->getId()]);
            return new Response("Created", 201, ["Location" => $poolUrl]);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Return a list of current pools
     *
     * @return object A symfony response object
     */
    public function returnPoolList()
    {
        $pools = $this->getList();

        $collection = new Collection($this->url("listPools"));
        
        $collection->addLink($this->homeLink());
        $collection->addLink($this->billboardLink());
        
        foreach ($pools as $pool) {
            $collection->addItem($this->createPoolItem($pool));
        }

        return $this->outputCollection($collection);
    }
    
    /**
     * Return a specific pool
     *
     * @param string $poolId The id of the pool to return
     * @return object A symfony response object
     */
    public function returnPool($poolId)
    {
        try {
            $pool = $this->get($poolId);
            
            $collection = new Collection(
                $this->url("getPool", ["poolId" => $poolId])
            );
            
            $collection->addLink($this->homeLink());
            $collection->addLink($this->billboardLink());
            $collection->addLink($this->listPoolsLink());
            $collection->addQuery($this->updatePoolQuery($poolId));
            $collection->addQuery($this->deletePoolQuery($poolId));
            
            $collection->addItem($this->createPoolItem($pool));
            return $this->outputCollection($collection);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Update a specific pool
     *
     * @param string $poolId The id of the pool to update
     * @param object $request A symfony request instance
     * @return object A symfony response instance
     */
    public function updatePool($poolId, Request $request)
    {
        /**
         * Get the json input from the request object and decode it to a useful
         * array
         */
        $input = json_decode($request->getContent());
        
        if (!isset($input->template->data)) {
            $collection = new Collection($this->url("updatePool", ["poolId" => $poolId]));
            $collection->setError($this->malformedRequestError());
            return $this->outputCollection($collection, 400);
        }
        $input = $this->dataToArray($input->template->data);
        
        try {
            $this->update($poolId, $input);
            return new Response("", 200);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Delete a specific pool
     *
     * @param string $poolId The id of the pool to delete
     * @return object A symfony response instance
     */
    public function deletePool($poolId)
    {
        try {
            $this->delete($poolId);
            return new Response("", 204);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get the link to the billboard
     *
     * @return object A link instance
     */
    private function billboardLink()
    {
        return new Property\Link(
            $this->url("poolsBillboard"),
            "billboard"
        );
    }
    
    /**
     * Get the link for listing all pools
     *
     * @return object A link instance
     */
    private function listPoolsLink()
    {
        return new Property\Link(
            $this->url("listPools"),
            "index",
            "poolsList",
            null,
            "List Pools"
        );
    }
    
    /**
     * Get the link for retrieving a specific pool
     * 
     * @param string $poolId The id of the pool (optional, default: {poolId})
     * @return object A query instance
     */
    private function getPoolLink($poolId = "{poolId}")
    {
        $link = new Property\Link(
            urldecode($this->url("getPool", ["poolId" => $poolId])),
            ""
        );
        
        if ($poolId == "{poolId}") {
            $link->setRel("template");
            return $link;
        }
        
        $pool = $this->getPool($poolId);
        $link->setRel("item");
        $link->setName($pool->getName());
        $link->setPrompt($pool->getName());
        
        return $link;
    }
    
    /**
     * Get the query for creating a new pool
     *
     * @return object A query instance
     */
    private function createPoolQuery()
    {
        $model = new PoolModel();
        $query = new Property\Query(
            $this->url("createPool"),
            "create-form"
        );
        $query->setPrompt("Create");
        foreach ($model->toArray() as $key => $value) {
            $data = new Property\Data(
                $key,
                $value,
                $model->getPrompt($key)
            );
            $query->addData($data);
        }
        return $query;
    }
    
    /**
     * Get the query for updating a pool
     *
     * @param string $poolId The id of the pool (optional, default: {poolId})
     * @return object A query instance
     */
    private function updatePoolQuery($poolId = "{poolId}")
    {
        $model = new PoolModel();
        $query = new Property\Query(
            urldecode($this->url("updatePool", ["poolId" => $poolId])),
            "edit-form"
        );
        
        if ($poolId == "{poolId}") {
            $query->setRel("query-template");
        }
        
        $query->setPrompt("Update");
        foreach ($model->toArray() as $key => $value) {
            $data = new Property\Data(
                $key,
                $value,
                $model->getPrompt($key)
            );
            $query->addData($data);
        }
        return $query;
    }
    
    /**
     * Get the query for deleting a pool
     *
     * @param string $poolId The id of the pool (optional, default: {poolId})
     * @return object A query instance
     */
    private function deletePoolQuery($poolId = "{poolId}")
    {
        $query = new Property\Query(
            urldecode($this->url("deletePool", ["poolId" => $poolId])),
            "delete"
        );
        
        $query->setPrompt("Delete");
        
        if ($poolId == "{poolId}") {
            $query->setRel("query-template");
        }
        
        return $query;
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
            $data = new Property\Data($key, $value, $pool->getPrompt($key));
            $item->addData($data);
        }
        
        return $item;
    }
}
