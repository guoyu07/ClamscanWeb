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
        $collection->addQuery($this->updateServerQuery());
        $collection->addQuery($this->deleteServerQuery());
        return $this->outputCollection($collection);
    }
    
    /**
     *
     */
    public function createServerTemplate(Request $request)
    {
        $collection = new Collection($this->url("createServer"));
        $collection->addLink($this->homeLink());
        $collection->addLink($this->listServersLink());
        $collection->addLink($this->getServerLink());
        $collection->addQuery($this->createServerQuery());
        return $this->outputCollection($collection);
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
        
        $collection->addLink($this->billboardLink());
        $collection->addLink($this->listServersLink());
        $collection->addQuery($this->createServerQuery());
        $collection->addQuery($this->updateServerQuery());
        $collection->addQuery($this->deleteServerQuery());
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
            
            $collection->addLink($this->billboardLink());
            $collection->addLink($this->listServersLink());
            $collection->addQuery($this->createServerQuery());
            $collection->addQuery($this->updateServerQuery($serverId));
            $collection->addQuery($this->deleteServerQuery($serverId));
            
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
            $this->update($serverId, $input);
            
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
     * Get the link to the billboard
     *
     * @return object A link instance
     */
    private function billboardLink()
    {
        return new Property\Link(
            $this->url("serversBillboard"),
            "home",
            "billboardLink",
            null,
            "Servers Home"
        );
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
            "index",
            "serversList",
            null,
            "List Servers"
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
        $link = new Property\Link(
            urldecode($this->url("getServer", ["serverId" => $serverId])),
            ""
        );
        
        if ($serverId == "{serverId}") {
            $link->setRel("template");
            return $link;
        }
        
        $server = $this->getServer();
        $link->setRel("item");
        $link->setName($server->getName());
        $link->setPrompt($server->getName());
        
        return $link;
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
     * Get the query for updating a server
     *
     * @param string $serverId The id of the server (optional, default: {serverId})
     * @return object A query instance
     */
    private function updateServerQuery($serverId = "{serverId}")
    {
        $model = new ServerModel();
        $query = new Property\Query(
            urldecode($this->url("updateServer", ["serverId" => $serverId])),
            "edit-form"
        );
        
        if ($serverId == "{serverId}") {
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
     * Get the query for deleting a server
     *
     * @param string $serverId The id of the server (optional, default: {serverId})
     * @return object A query instance
     */
    private function deleteServerQuery($serverId = "{serverId}")
    {
        return new Property\Query(
            urldecode($this->url("deleteServer", ["serverId" => $serverId])),
            "query-template",
            "",
            "Delete"
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
