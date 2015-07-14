<?php
/**
 * This file contains the Job database model for this application
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a job within the ClamScanWeb application
 * @author Anthony Vitacco <avitacco@iu.edu>
 *
 * @ORM\Entity
 * @ORM\Table(name="ClamScanJobs")
 */
class Job
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private $id;
    
    /** @ORM\Column(type="datetimetz") */
    private $addedAt;
    
    /** @ORM\Column(type="string", length=8) */
    private $addedBy;
    
    /** @ORM\Column(type="string", length=12) */
    private $state;
    
    /** @ORM\Column(type="string", length=32) */
    private $username;
    
    /** @ORM\Column(type="string", length=64) */
    private $reportAddress;
    
    /** @ORM\Column(type="array") */
    private $excludeDirs;
    
    /** @ORM\Column(type="array") */
    private $excludeFiles;
    
    /** @ORM\Column(type="boolean") */
    private $logAllFiles = false;
    
    /** @ORM\Column(type="guid", nullable=true) */
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
            "result" => $this->result,
        ];
    }
}
