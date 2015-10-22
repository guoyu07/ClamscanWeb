<?php
/**
 * This file contains the FieldInfo annotation class
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Annotations;

/**
 * This is the field information annotation class. It is used to define
 * information for database models to create html inputs on front end pages.
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 * 
 * @Annotation
 * @Attributes({
 *  @Attribute("type", required=true, type="string"),
 *  @Attribute("prompt", required=true, type="string"),
 *  @Attribute("required", required=false, type="boolean"),
 *  @Attribute("defaultVal", required=false, type="string"),
 *  @Attribute("options", required=false, type="array"),
 *  @Attribute("placeholder", required=false, type="string")
 * })
 */
class FieldInfo {
    /** @var string */
    public $type;
    
    /** @var string */
    public $prompt;
    
    /** @var boolean */
    public $required = false;
    
    /** @var string */
    public $defaultVal;
    
    /** @var array */
    public $options;
    
    /** @var string */
    public $placeholder;
    
    /**
     * Annotation constructor
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->type = $values["type"];
        $this->prompt = $values["prompt"];
        
        /** Set the required value if it was given */
        if (isset($values["required"])) {
            $this->required = $values["required"];
        }
        
        /** Set the "default" value if it was given */
        if (isset($values["defaultVal"])) {
            $this->defaultVal = $values["defaultVal"];
        }
        
        /** Set the options value if it was given */
        if (isset($values["options"])) {
            $this->options = $values["options"];
        }
        
        /** Set the placeholder value if it was given */
        if (isset($values["placeholder"])) {
            $this->placeholder = $values["placeholder"];
        }
    }
}

