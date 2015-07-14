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
    
    /** @Column(type="smallint", length=16, nullable=true) */
    private $scannedDirectories;
    
    /** @Column(type="smallint", length=16, nullable=true) */
    private $scannedFiles;
    
    /** @Column(type="smallint", length=8, nullable=true) */
    private $infectedFiles;
    
    /** @Column(type="string", nullable=true) */
    private $dataScanned;
    
    /** @Column(type="string", nullable=true) */
    private $dataRead;
    
    /** @Column(type="string", nullable=true) */
    private $executionTime;
    
    /** @Column(type="json_array", nullable=true) */
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
            "infectedFiles" => $this->infectedFiles,
            "dataScanned" => $this->dataScanned,
            "dataRead" => $this->dataRead,
            "executionTime" => $this->executionTime,
            "fileResults" => $this->fileResults,
        ];
    }
}
