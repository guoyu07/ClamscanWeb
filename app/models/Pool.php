<?php

namespace Iu\Uits\Webtech\ClamScanWeb\Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="pools")
 */
class Pool implements \JsonSerializable
{
    /** @ODM\Id */
    private $id;
    
    /** @ODM\Field(type="string") */
    private $name;
    
    /** @ODM\ReferenceMany(targetDocument="Server", cascade={"all"}) */
    private $servers = [];
    
    /**
     * Get Id
     *
     * @return string The Id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get Name
     *
     * @return string The pool name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set Name
     *
     * @param string $value The pool name
     */
    public function setName($value)
    {
        $this->name = $value;
    }
    
    /**
     * Get Servers
     *
     * @return array The list of servers
     */
    public function getServers()
    {
        return $this->servers;
    }
    
    /**
     * Add a server
     *
     * @param object $server The server object
     */
    public function addServer($server)
    {
        $this->servers[] = $server;
    }
    
    /**
     * Remove a server
     *
     * @param object $server The server object
     */
    public function removeServer($server)
    {
        /** Maybe this isn't needed? */
    }
    
    /**
     * Set the servers array
     *
     * @param array $servers An array of server objects
     */
    public function setServers(array $servers)
    {
        $this->servers = $servers;
    }
    
    /**
     * This function returns all the class scope variables as an array
     *
     * @return array The pool information as an array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "servers" => $this->servers
        ];
    }
    
    /**
     * This function returns the prompt for a given field
     *
     * @param string $field The field
     * @return string The prompt
     */
    public function getPrompt($field)
    {
        $prompts = [
            "id" => "Id",
            "name" => "Name",
            "servers" => "Servers",
        ];
        
        return $prompts[$field];
    }
    
    /**
     * This function is called magically when this class is json_serialize'd
     *
     * @return array The information we want serialized
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
