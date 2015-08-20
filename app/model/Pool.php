<?php

namespace Iu\Uits\Webtech\ClamScanWeb\Models;

/**
 * @mongodb:Document(collection="pools")
 */
class Pool
{
    /** @mongodb:Id */
    private $id;
    
    /** @mongodb:Field(type="string") */
    private $name;
    
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