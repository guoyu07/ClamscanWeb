<?php
/**
 * This file contains the Result database model for this application
 * @license MIT
 */
namespace Iu\Uits\Webtech\Clam\Model;

/**
 * This class represents a result for a job executed by the ClamScanWeb
 * application.
 * @author Anthony Vitacco <avitacco@iu.edu>
 *
 * @Entity
 * @Table(name="ClamScanResults")
 */
class Result
{
    /** @Id @Column(type="guid") */
    private $id;
    
    /** @Column(type="datetimetz") */
    private $completedAt;
    
    /** @Column(type="smallint", length=16) */
    private $scannedDirectories;
    
    /** @Column(type="smallint", length=16) */
    private $scannedFiles;
    
    /** @Column(type="smallint", length=8) */
    private $infectedFiles;
    
    /** @Column(type="string") */
    private $dataScanned;
    
    /** @Column(type="string") */
    private $dataRead;
    
    /** @Column(type="string") */
    private $executionTime;
    
    /** @Column(type="json_array") */
    private $fileResults;
    
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
            "completedAt" => $this->completedAt,
            "scannedDirectories" => $this->scannedDirectories,
            "scannedFiles" => $this->scannedFiles,
            "dataScanned" => $this->dataScanned,
            "dataRead" => $this->dataRead,
            "executionTime" => $this->executionTime,
            "fileResults" => $this->fileResults,
        ];
    }
}
