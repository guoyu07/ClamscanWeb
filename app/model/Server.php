<?php

namespace Iu\Uits\Webtech\ClamScanWeb\Models;

Use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="servers")
 */
class Server
{
    /** @ODM\Id */
    private $id;
    
    /** @ODM\Field(type="string") */
    private $name;
    
    /** @ODM\Field(type="string") */
    private $address;
    
    /** @ODM\Field(type="int") */
    private $port = 22;
    
    /** @ODM\Field(type="string") */
    private $authMethod;
    
    /** @ODM\Field(type="string") */
    private $username;
    
    /** @ODM\Field(type="string") */
    private $password;
    
    /** @ODM\Field(type="string") */
    private $publicKey;
    
    /** @ODM\Field(type="string") */
    private $privateKey;
    
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
}
