<?php
/**
 * This file contains the Pool api controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Api;

use Iu\Uits\Webtech\ClamScanWeb\Controllers\Pool as PoolController;
use Iu\Uits\Webtech\ClamScanWeb\Models\Pool as PoolModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CollectionJson\Collection;
use CollectionJson\Property;

/**
 * This class provides collection+json functionality for the Pool controller
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Pool extends PoolController
{
    /** Use the CollectionHelper trait */
    use \Iu\Uits\Webtech\ClamScanWeb\Traits\CollectionHelper;
    
    /** @var object The silex application instance */
    private $app;
    
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
        $collection->addLink($this->listLink());
        $collection->addLink($this->getServerLink());
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
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
        
        /**
         * If everything went right, we should send the correct response
         */
        $poolUrl = $this->url("getPool", ["poolId" => $pool->getId()]);
        return new Response(
            "Created",
            201,
            ["Location" => $poolUrl]
        );
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
        $pool = $this->get($poolId);
        
        $collection = new Collection(
            $this->url(
                "getPool",
                ["poolId" => $poolId]
            )
        );
        
        if (is_null($pool)) {
            $collection->setError($this->poolNotFoundError());
            return $this->outputCollection($collection, 404);
        }
        
        $collection->addItem($this->createPoolItem($pool));
        return $this->outputCollection($collection);
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
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
        
        return new Response("", 200);
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
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
        
        return new Response("", 204);
    }
    
    /**
     * Since the code for handling exceptions is pretty much the same regardless
     * of what function being run, it's easiest to just have all of it in a
     * function
     *
     * @param object $e The exception
     * @return object A symfony response instance
     */
    private function handleException($e)
    {
        $collection = new Collection($this->url("createPool"));
        switch ($e->getCode()) {
            case 400:
                $error = $this->malformedRequestError();
                break;
            case 404:
                $error = $this->poolNotFoundError();
                break;
            case 409:
                $error = $this->poolExistsError();
                break;
            default:
                $error = $this->genericOrUnknownError();
                break;
        }
        $collection->setError($error);
        return $this->outputCollection($collection, $e->getCode());
    }
    
    /**
     * Return a new malformed request error object
     *
     * @return object An error instance
     */
    private function malformedRequestError()
    {
        return new Collection\Error(
            "Malformed Request",
            400,
            "The request contained invalid collection+json"
        );
    }
    
    /**
     * Return a new pool not found error object
     *
     * @return object An error instance
     */
    private function poolNotFoundError()
    {
        return new Collection\Error(
            "Unknown Pool Id",
            404,
            "The given pool was not found"
        );
    }
    
    /**
     * Return a new pool exists error object
     *
     * @return object An error instance
     */
    private function poolExistsError()
    {
        return new Collection\Error(
            "Conflict",
            409,
            "A pool with that name already exists"
        );
    }
    
    /**
     * Return a generic or unknown error object
     *
     * @return object An error instance
     */
    private function genericOrUnknownError()
    {
        return new Collection\Error(
            "Unknown Error",
            999,
            "An unknown error has occurred"
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
     * Get the link for listing all pools
     *
     * @return object A link object for the list all pools link
     */
    private function listLink()
    {
        return new Property\Link(
            $this->url("listPools"),
            "index"
        );
    }
    
    /**
     * Get the link for retrieving a specific pool
     *
     * @return object A link object for the get pool link
     */
    private function getServerLink()
    {
        return new Property\Link(
            urldecode($this->url("getPool", ["poolId" => "{poolId}"])),
            "item"
        );
    }
}
