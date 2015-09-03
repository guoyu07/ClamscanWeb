<?php
/**
 * This file is the server model for ClamScan Web
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="servers")
 * @author Anthony Vitacco <avitacco@.iu.edu>
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
     * Get Address
     *
     * @return string The address
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * Set Address
     *
     * @param string $address The address
     */
    public function setAddress($value)
    {
        $this->address = $value;
    }
    
    /**
     * Get Port
     *
     * @return int The port number
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * Set Port
     *
     * @param int $value The port number
     */
    public function setPort($value)
    {
        $this->port = (int)$value;
    }
    
    /**
     * Get Auth Method
     *
     * @return string The auth method
     */
    public function getAuthMethod()
    {
        return $this->authMethod;
    }
    
    /**
     * Set Auth Method
     *
     * @param string $value The auth method
     */
    public function setAuthMethod($value)
    {
        $this->authMethod = $value;
    }
    
    /**
     * Get Username
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Set Username
     *
     * @param string $value The username
     */
    public function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     * Get Password
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Set Password
     *
     * @param string $password The password
     */
    public function setPassword($value)
    {
        $this->password = $value;
    }
    
    /**
     * Get Public Key
     *
     * @return string The public key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
    
    /**
     * Set Public Key
     *
     * @param string $value The public key
     */
    public function setPublicKey($value)
    {
        $this->publicKey = $value;
    }
    
    /**
     * Get Private Key
     *
     * @return string The private key
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }
    
    /**
     * Set Private Key
     *
     * @param string $value The private key
     */
    public function setPrivateKey($value)
    {
        $this->privateKey = $value;
    }
    
    /**
     * This function returns all class scope variables as an array
     *
     * @return array The values from this class
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
     * This function returns the prompt for a field
     *
     * @param string $field The field
     * @return string The prompt for the given field
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
     * This function returns the information to be serialized as json
     *
     * @return array The information
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
