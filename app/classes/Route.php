<?php
/**
 *
 */
namespace \Iu\Uits\Webtech;

use Silex\Route as SilexRoute;

/**
 *
 */
class Route extends SilexRoute
{
    public function accept(array $contentTypes)
    {
        foreach ($contentTypes as $key => $contentType) {
            $contentTypes[$key] = preg_quote($contentType, "/");
        }
        
        $contentTypeRegex = implode("|", $contentTypes);
        $this->setRequirement("accept", $contentTypeRegex);
    }
}
