<?php
/**
 * This file contains the Job database model for this application
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam\Model;

/**
 * This class represents a job within the ClamScanWeb application
 * @author Anthony Vitacco <avitacco@iu.edu>
 *
 * @Entity
 * @Table(name="ClamScanJobs")
 */
class Job
{
    /** @Id @Column(type="integer") @GeneratedValue */
    private $id;
    
    /** @Column(type="datetime") */
    private $addedAt;
    
    /** @Column(type="text", length=12) */
    private $state;
    
    /** @Column(type="text", length=32) */
    private $username;
    
    /** @Column(type="text", length=64) */
    private $reportAddress;
    
    /** @Column(type="array") */
    private $excludeDirs;
    
    /** @Column(type="array") */
    private $excludeFiles;
    
    /**
     * Magic set function, sets keys for this class
     *
     * @param string $name The name of the variable to set
     * @param mixed $value The value which to set it
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    
    /**
     * Magic get function, gets values for this class
     *
     * @param string $name The name of the variable to get
     * @return mixed The value of the variable
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
    }
}