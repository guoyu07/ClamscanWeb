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
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue */
    private $id;
    
    /** @Column(type="datetimetz") */
    private $addedAt;
    
    /** @Column(type="string", length=8) */
    private $addedBy;
    
    /** @Column(type="string", length=12) */
    private $state;
    
    /** @Column(type="string", length=32) */
    private $username;
    
    /** @Column(type="string", length=64) */
    private $reportAddress;
    
    /** @Column(type="array") */
    private $excludeDirs;
    
    /** @Column(type="array") */
    private $excludeFiles;
    
    /** @Column(type="boolean") */
    private $logAllFiles = false;
    
    /** @Column(type="guid", nullable=true) */
    private $result;
    
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
    
    /**
     * Return the private data in this object as an array
     *
     * @return array The private object properties as an array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "addedAt" => $this->addedAt,
            "addedBy" => $this->addedBy,
            "state" => $this->state,
            "username" => $this->username,
            "reportAddress" => $this->reportAddress,
            "excludeDirs" => $this->excludeDirs,
            "excludeFiles" => $this->excludeFiles,
            "logAllFiles" => $this->logAllFiles,
            "result" => $this->result,
        ];
    }
}
