<?php
/**
 * This file contains the CollectionHelper trait used in the Clamscan Web
 * application
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Traits;

/**
 * This trait provides some useful helper classes for dealing with collection
 * objects
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
trait CollectionHelper
{
    /**
     * Create a response object out of the collection with the correct content
     * type to send collection+json
     *
     * @param object The collection
     * @param int The status code to send (default: 200)
     * @return object A symfony response object
     */
    private function outputCollection(\CollectionJson\Collection $collection, $code = 200)
    {
        return $this->app->json(
            $collection->toArray(),
            $code,
            ["Content-Type" => "application/vnd.collection+json"]
        );
    }
    
    /**
     * The symfony url generator is nice and very convenient, but it's also
     * sometimes a bit long-winded to use
     *
     * @param string $route The name of the route to generate a url for
     * @param array $params The array of route parameters (default: empty array)
     * @return string The url requested
     */
    private function url($route, $params = [])
    {
        return $this->app["url_generator"]->generate(
            $route,
            $params,
            \Symfony\Component\Routing\Generator\UrlGenerator::ABSOLUTE_URL
        );
    }
    
    /**
     * This function gets data from the data array of the submission
     *
     * @param string $name The name of the data to get
     * @param mixed $input The collection from which to get the data
     * @return mixed The requested data or false on failure
     */
    private function getData($name, $input)
    {
        /**
         * Since we're using array_ functions, the data we're using needs to be an
         * array too
         */
        if (is_object($input)) {
            $input = json_decode(json_encode($input), true);
        }
        
        /**
         * The data was formatted incorrectly when it was sent in or didn't
         * include the right stuff
         */
        if (!isset($input["template"]["data"])) {
            return false;
        }
        
        $dataItems = $input["template"]["data"];
        $key = array_search($name, array_column($dataItems, "name"));
        
        if ($key !== false) {
            return $dataItems[$key];
        }
        
        return false;
    }
    
    /**
     *
     */
    private function dataToArray($input)
    {
        $output = [];
        foreach ($input as $datum) {
            $output[$datum->name] = $datum->value;
        }
        return $output;
    }
    
    /**
     * Since the code for handling exceptions is pretty much the same regardless
     * of what function being run, it's easiest to just have all of it in a
     * function
     *
     * @param object $e The exception
     * @return object A symfony response instance
     */
    private function handleException($e)
    {
        $collection = new Collection($this->url("createPool"));
        switch ($e->getCode()) {
            case 400:
                $error =  new Collection\Error(
                    "Malformed Request",
                    400,
                    "The request contained invalid collection+json"
                );
                break;
            case 404:
                $error = new Collection\Error(
                    "Not Found",
                    404,
                    "The requested item was not found"
                );
                break;
            case 409:
                $error = new Collection\Error(
                    "Conflict",
                    409,
                    "An item with that name already exists"
                );
                break;
            default:
                $error = new Collection\Error(
                    "Unknown Error",
                    999,
                    "An unknown error has occurred"
                );
                break;
        }
        $collection->setError($error);
        return $this->outputCollection($collection, $e->getCode());
    }
}
