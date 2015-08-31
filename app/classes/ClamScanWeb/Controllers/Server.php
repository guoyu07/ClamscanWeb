<?php
/**
 * This file contains the Server controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Iu\Uits\Webtech\ClamScanWeb\Models\Server as ServerModel;
use CollectionJson\Collection;
use CollectionJson\Property;

/**
 * Server controller class
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Server
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
     *
     */
    public function billboard()
    {
        $collection = new Collection($this->url("serversBillboard"));
        
        $listServersLink = new Property\Link(
            $this->url("listServers"),
            "index"
        );
        $collection->addLink($listServersLink);
        
        $getServerLink = new Property\Link(
            urldecode($this->url("getServer", ["serverId" => "{serverId}"])),
            "item"
        );
        $collection->addLink($getServerLink);
        
        $server = new ServerModel();
        
        /**
         * Build a query object for creating a server
         */
        $newServerQuery = new Property\Query($this->url("createServer"), "create-form");
        $newServerQuery->getHref($this->url("createServer"));
        foreach ($server->toArray() as $key => $value) {    
            $data = new Property\Data($key, $value, $server->getPrompt($key));
            $newServerQuery->addData($data);
        }
        $collection->addQuery($newServerQuery);
        
        /**
         * Build a query for updating a server
         */
        $editServerUrl = urldecode($this->url("updateServer", ["serverId" => "{serverId}"]));
        $editServerQuery = new Property\Query($editServerUrl, "edit-form");
        foreach ($server->toArray() as $key => $value) {
            $data = new Property\Data($key, $value, $server->getPrompt($key));
            $editServerQuery->addData($data);
        }
        $collection->addQuery($editServerQuery);
        
        /**
         * Build a query for deleting a server
         */
        $deleteServerUrl = urldecode($this->url("deleteServer", ["serverId" => "{serverId}"]));
        $deleteServerQuery = new Property\Query($deleteServerUrl, "delete");
        $collection->addQuery($deleteServerQuery);
        
        /**
         * Finally, return the collection with the correct content type
         */
        return $this->app->json(
            $collection->toArray(),
            200,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * Create a server
     *
     * @param object $request A symfony request object
     */
    public function create(Request $request)
    {
        $input = json_decode($request->getContent());
        
        /**
         * If we're unable to get the name data element from the request input
         * that means we were given a bad collection+json
         */
        if (!$this->getData("name", $input)) {
            $collection = new Collection($this->url("createServer"));
            $collection->setError(new Collection\Error(
                "Malformed Request",
                400,
                "The json input was malformed"
            ));
            return $this->app->json($collection->toArray());
        }
        
        $server = new ServerModel();
        
        foreach ($server->toArray() as $key => $value) {
            $functionName = "set" . ucfirst($key);
            $value = $this->getData($key, $input);
            if ($functionName != "setId" && $value) {
                $server->$functionName($value["value"]);
            }
        }
        
        $dm = $this->app["deps"]["mongoDm"];
        $dm->persist($server);
        $dm->flush();
        
        /**
         * Now that the server has been persisted, it has an ID and we can use
         * that to create a url to get to it.
         */
        $serverUrl = $this->url("getServer", ["serverId" => $server->getId()]);
        $response = new Response(
            "Created",
            201,
            ["Location" => $serverUrl]
        );
        return $response;
    }
    
    /**
     * Get a list of all servers
     *
     * @return object A symfony json response object
     */
    public function getList()
    {
        $dm = $this->app["deps"]["mongoDm"];
        $servers = $dm->createQueryBuilder("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->eagerCursor(true)
        ->getQuery()
        ->execute();
        
        $collection = new Collection($this->url("listServers"));
        
        foreach ($servers as $server) {
            $collection->addItem($this->createServerItem($server));
        }
        
        return $this->app->json(
            $collection->toArray(),
            200,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * Get a specific server by its id
     *
     * @param string $serverId The ID of the server to get
     * @return object A symfony response object
     */
    public function get($serverId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $server = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy(["id" => $serverId]);
        
        $collection = new Collection($this->url("getServer", ["serverId" => $serverId]));
        
        /**
         * We were given a bad server id to look up
         */
        if (is_null($server)) {
            $collection->setError(new Collection\Error(
                "Unknown Server Id",
                404,
                "The given server was not found"
            ));
            return $this->app->json(
                $collection->toArray(),
                404,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        $collection->addItem($this->createServerItem($server));
        return $this->app->json(
            $collection->toArray(),
            200,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * Update a server
     *
     * @param string $serverId The Id of the server to update
     * @param object The symfony request object
     * @return object A symfony response object
     */
    public function update($serverId, Request $request)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $server = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy(["id" => $serverId]);
        $input = json_decode($request->getContent());
        
        $collection = new Collection($this->url("getServer", ["serverId" => $serverId]));
        
        /**
         * We were given a bad server id to look up
         */
        if (is_null($server)) {
            $collection->setError(new Collection\Error(
                "Unknown Server Id",
                404,
                "The given server was not found"
            ));
            return $this->app->json(
                $collection->toArray(),
                404,
                ["Content-Type" => "application/vnd.collection+json"]
            );
        }
        
        /**
         * Loop through all fields in the server model and try to get the data
         * for it. If there is data, update the value of the field
         */
        foreach ($server->toArray() as $key => $value) {
            $functionName = "set" . ucfirst($key);
            $value = $this->getData($key, $input);
            if ($functionName != "setId" && $value) {
                $server->$functionName($value["value"]);
            }
        }
        
        /**
         * Persist the changes
         */
        $dm = $this->app["deps"]["mongoDm"];
        $dm->persist($server);
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
     * Delete a server
     *
     * @param string $serverId The server id
     * @return object A symfony response object
     */
    public function delete($serverId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $server = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy(["id" => $serverId]);
        
        /**
         * We were given a bad server id to look up
         */
        if (is_null($server)) {
            $collection = new Collection($this->url("deleteServer", ["serverId" => $serverId]));
            $collection->setError(new Collection\Error(
                "Unknown Server Id",
                404,
                "The given server was not found"
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
        $dm->remove($server);
        $dm->flush();
        
        /**
         * Now that the pool is deleted, we send the response about what
         * happened
         */
        $response = new Response("", 204);
        return $response;
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
     * Because there are several times where we need to create items out of 
     * server classes, it's better to have a function to handle that.
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
