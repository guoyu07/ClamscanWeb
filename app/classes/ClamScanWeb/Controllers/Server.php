<?php
/**
 * This file contains the Server controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers;

use Iu\Uits\Webtech\ClamScanWeb\Models\Server as ServerModel;
use Symfony\Component\HttpFoundation\Response;

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
     * Create a server
     *
     * @param array $input The data required to create a new server
     * @return object The server which was just created
     */
    public function create($input)
    {
        $server = new ServerModel();
        
        /** Servers need names */
        if (!isset($input["name"])) {
            throw new \RuntimeException("Malformed Request", 400);
        }
        
        /** Servers need unique names */
        if ($this->exists("name", $input["name"])) {
            throw new \RuntimeException("Conflict", 409);
        }
        
        foreach ($server->toArray() as $key => $value) {
            $functionName = "set" . ucfirst($key);
            
            if (isset($input[$key]) && $key != "id") {
                $server->$functionName($input[$key]);
            }
        }
        
        $this->app["deps"]["mongoDm"]->persist($server);
        $this->app["deps"]["mongoDm"]->flush();
        
        return $server;
    }
    
    /**
     * Get a list of all servers
     *
     * @return object An instance of mongodb eagercursor result set
     */
    public function getList()
    {
        $dm = $this->app["deps"]["mongoDm"];
        $servers = $dm->createQueryBuilder("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->eagerCursor(true)
        ->getQuery()
        ->execute();
        
        return $servers;
    }
    
    /**
     * Get a specific server by its id
     *
     * @param string $serverId The ID of the server to get
     * @return object The requested server object
     */
    public function get($serverId)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $server = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy(["id" => $serverId]);
        
        if (is_null($server)) {
            throw new \RuntimeException("Not Found", 404);
        }
        
        return $server;
    }
    
    /**
     * Update a server
     *
     * @param string $serverId The Id of the server to update
     * @param array $input The input needed to change the server
     * @return object The updated server object
     */
    public function update($serverId, $input)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $server = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy(["id" => $serverId]);
        
        /**
         * We were given a bad server id to look up
         */
        if (is_null($server)) {
            throw new \RuntimeException("Not found", 404);
        }
        
        /**
         * Server names MUST be unique
         */
        if (isset($input["name"]) && $this->exists("name", $input["name"])) {
            throw new \RuntimeException("Conflict", 409);
        }
        
        /**
         * Loop through all fields in the server model and try to get the data
         * for it. If there is data, update the value of the field
         */
        foreach ($server->toArray() as $key => $value) {
            $functionName = "set" . ucfirst($key);
            
            if (isset($input[$key]) && $key != "id") {
                $server->$functionName($input[$key]);
            }
        }
        
        /**
         * Persist the changes
         */
        $this->app["deps"]["mongoDm"]->persist($server);
        $this->app["deps"]["mongoDm"]->flush();
        
        /**
         * Return the changed server object
         */
        return $server;
    }
    
    /**
     * Delete a server
     *
     * @param string $serverId The server id
     * @return object The deleted server object
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
            throw new \RuntimeException("Not found", 404);
        }
        
        /**
         * Remove the document and flush the document manager
         */
        $dm->remove($server);
        $dm->flush();
        
        /**
         * Return the deleted server
         */
        return $server;
    }
    
    /**
     * Check to see if a server exists already with a field set to a specific
     * value
     *
     * @param string $field The name of the field
     * @param string $value The value of the field
     */
    private function exists($field, $value)
    {
        $dm = $this->app["deps"]["mongoDm"];
        $results = $dm->getRepository("Iu\Uits\Webtech\ClamScanWeb\Models\Server")
        ->findOneBy([$field => $value]);
        
        return (boolean)count($results);
    }
    
}
