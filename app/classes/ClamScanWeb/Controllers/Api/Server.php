<?php
/**
 * This file contains the Server api controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Api;

use CollectionJson\Collection;
use CollectionJson\Property;
use Iu\Uits\Webtech\ClamScanWeb\Controllers\Server as ServerController;
use Iu\Uits\Webtech\ClamScanWeb\Models\Server as ServerModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class extends the Server controller class to provide collection+json
 * functionality for that controller
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Server extends ServerController
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
     * Return the billboard for the servers
     *
     * @return object A symfony response instance
     */
    public function billboard()
    {
        $collection = new Collection($this->url("serversBillboard"));
        $collection->addLink($this->listServersLink());
        $collection->addLink($this->getServerLink());
        $collection->addQuery($this->createServerQuery());
        $collection->addQuery($this->editServerQuery());
        $collection->addQuery($this->deleteServerQuery());
        $this->outputCollection($collection->toArray());
    }
    
    /**
     * Create a new server
     *
     * @param object $request A symfony request instance
     * @return object A symfony response instance
     */
    public function createServer(Request $request)
    {
        /**
         * Take the input and decode it to a more useful array
         */
        $input = json_decode($request->getContent());
        
        /**
         * If we don't have data, there's jack we can do anyway
         */
        if (!isset($input->template->data)) {
            return $this->handleException(
                new \RuntimeException("Malformed Request", 400)
            );
        }
        
        $input = $this->dataToArray($input->template->data);
        
        try {
            $server = $this->create($input);
            
            $serverUrl = $this->url("getServer", ["serverId" => $server->getId()]);
            return new Response("Created", 201, ["Location" => $serverUrl]);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Return a list of available servers
     *
     * @return object A symfony response instance
     */
    public function returnServerList()
    {
        $servers = $this->getList();
        
        $collection = new Collection($this->url("listServers"));
        
        foreach ($servers as $server) {
            $collection->addItem(
                $this->createServerItem($server)
            );
        }
        
        return $this->outputCollection($collection);
    }
    
    /**
     * Return a specific server
     *
     * @param string $serverId
     * @return object A symfony response instance
     */
    public function returnServer($serverId)
    {
        try {
            $server = $this->get($serverId);
            
            $collection = new Collection(
                $this->url("getServer", ["serverId" => $serverId])
            );
            
            $collection->addItem($this->createServerItem($server));
            
            return $this->outputCollection($collection);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
        
    }
    
    /**
     * Update a specific server
     *
     * @param string $serverId The id of the server to update
     * @param object $request A symfony request instance
     * @return object A symfony response instance
     */
    public function updateServer($serverId, Request $request)
    {
        /**
         * Take the input and decode it to a more useful array
         */
        $input = json_decode($request->getContent());
        
        /**
         * If we don't have data, there's jack we can do anyway
         */
        if (!isset($input->template->data)) {
            return $this->handleException(
                new \RuntimeException("Malformed Request", 400)
            );
        }
        
        $input = $this->dataToArray($input->template->data);
        
        try {
            $server = $this->update($serverId, $input);
            
            return new Response("", 200);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Delete a specific server
     *
     * @param string $serverId The id of the server to delete
     * @return object A symfony response instance
     */
    public function deleteServer($serverId)
    {
        try {
            $this->delete($serverId);
            return new Response("", 204);
        } catch (\RuntimeException $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get the link for listing all servers
     *
     * @return object A link instance
     */
    private function listServersLink()
    {
        return new Property\Link(
            $this->url("listServers"),
            "index"
        );
    }
    
    /**
     * Get the link for retrieving a specific server
     *
     * @param string $serverId The id of the server (optional, default: {serverId})
     * @return object A link instance
     */
    private function getServerLink($serverId = "{serverId}")
    {
        return new Property\Link(
            urldecode($this->url("getServer", ["serverId" => $serverId])),
            "item"
        );
    }
    
    /**
     * Get the query for creating a new server
     *
     * @return object A query instance
     */
    private function createServerQuery()
    {
        $model = new ServerModel();
        $query = new Property\Query(
            $this->url("createServer"),
            "create-form"
        );
        foreach ($model as $key => $value) {
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
     * Get the query for editing a server
     *
     * @param string $serverId The id of the server (optional, default: {serverId})
     * @return object A query instance
     */
    private function editServerQuery($serverId = "{serverId}")
    {
        $model = new ServerModel();
        $query = new Property\Query(
            urldecode($this->url("editServer", ["serverId" => $serverId])),
            "edit-form"
        );
        foreach ($model as $key => $value) {
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
     * Get the query for deleting a server
     *
     * @param string $serverId The id of the server (optional, default: {serverId})
     * @return object A query instance
     */
    private function deleteServerQuery($serverId = "{serverId}")
    {
        return new Property\Query(
            urldecode($this->url("deleteServer", ["serverId" => $serverId])),
            "delete"
        );
    }
    
    /**
     * Because there are several times where we need to create items out of 
     * server objects, it's better to have a function to handle that.
     *
     * @param object $server The server class
     * @return object A collection item instance
     */
    private function createServerItem(ServerModel $server)
    {
        $item = new Collection\Item(
            $this->url("getServer", ["serverId" => $server->getId()])
        );
        
        foreach ($server->toArray() as $key => $value) {
            $data = new Property\Data($key, $value);
            $item->addData($data);
        }
        
        return $item;
    }
}
