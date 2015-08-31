<?php

namespace Iu\Uits\Webtech\ClamScanWeb\Models;

Use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="servers")
 */
class Server implements \JsonSerializable
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
    
    /**
     *
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     *
     */
    public function setAddress($value)
    {
        $this->address = $value;
    }
    
    /**
     *
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     *
     */
    public function setPort($value)
    {
        $this->port = (int)$value;
    }
    
    /**
     *
     */
    public function getAuthMethod()
    {
        return $this->authMethod;
    }
    
    /**
     *
     */
    public function setAuthMethod($value)
    {
        $this->authMethod = $value;
    }
    
    /**
     *
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     *
     */
    public function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     *
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     *
     */
    public function setPassword($value)
    {
        $this->password = $value;
    }
    
    /**
     *
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
    
    /**
     *
     */
    public function setPublicKey($value)
    {
        $this->publicKey = $value;
    }
    
    /**
     *
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }
    
    /**
     *
     */
    public function setPrivateKey($value)
    {
        $this->privateKey = $value;
    }
    
    /**
     *
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "address" => $this->address,
            "port" => $this->port,
            "authMethod" => $this->authMethod,
            "username" => $this->username,
            "password" => $this->password,
            "publicKey" => $this->publicKey,
            "privateKey" => $this->privateKey,
        ];
    }
    
    /**
     *
     */
    public function getPrompt($field)
    {
        $prompts = [
            "id" => "",
            "name" => "Name",
            "address" => "Address",
            "port" => "Port (default: 22)",
            "authMethod" => "Authentication Method",
            "username" => "Username",
            "password" => "Password",
            "publicKey" => "Public Key",
            "privateKey" => "Private Key"
        ];
        
        return $prompts[$field];
    }
    
    /**
     *
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
