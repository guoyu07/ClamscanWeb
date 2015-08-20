<?php

namespace Iu\Uits\Webtech\ClamScanWeb\Models;

Use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="pools")
 */
class Pool
{
    /** @ODM\Id */
    private $id;
    
    /** @ODM\Field(type="string") */
    private $name;
    
    /** @ODM\ReferenceMany(targetDocument="Server") */
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
     *
     */
    public function addServer($server)
    {
        $this->servers[] = $server;
    }
    
}
