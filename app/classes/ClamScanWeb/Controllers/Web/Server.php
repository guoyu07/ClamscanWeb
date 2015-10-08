<?php
/**
 * This file contains the server web controller
 * @license MIT
 */
namespace Iu\Uits\Webtech\ClamScanWeb\Controllers\Web;

use Iu\Uits\Webtech\ClamScanWeb\Models\Server as ServerModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class provides functionality for the main page of the site
 *
 * @author Anthony Vitacco <avitacco@iu.edu>
 */
class Server
{
    /** Use the CollectionHelper trait */
    use \Iu\Uits\Webtech\ClamScanWeb\Traits\CollectionHelper;
    
    /** Use the WebHelper trait */
    use \Iu\Uits\Webtech\ClamScanWeb\Traits\WebHelper;
    
    /**
     * Magic constructor function
     *
     * @param object $app The application instance
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * Return the index page
     *
     * @param object $request A symfony request object
     * @return object A symfony response object
     */
    public function renderIndex(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        return $this->app->render("pages/servers/index.twig", $variables);
    }
    
    /**
     *
     */
    public function getCreatePage(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        return $this->app->render("pages/servers/create.twig", $variables);
    }
    
    /**
     *
     */
    public function createServer(Request $request)
    {
        //$subRequest = $this->generateCreateServerSubrequest($request);
        $subRequest = $this->generateWriteSubrequest(
            $request,
            $this->url("createServer"),
            "PUT"
        );
        $result = $this->app->handle($subRequest);
        
        $variables = $this->templateVars();
        $variables["status"] = $result->getStatusCode();
        
        switch ($result->getStatusCode()) {
            case 201:
                return $this->app->redirect($this->url("listServers"));
                break;
            case 409:
                $variables["message"] = [
                    "type" => "error",
                    "content" => "A server already exists by that name, please try a unique name."
                ];
                return $this->app->render("pages/error.twig", $variables);
                break;
            default:
                $variables["message"] = [
                    "type" => "error",
                    "content" => "There was an error while processing your request."
                ];
                return $this->app->render("pages/error.twig", $variables);
                break;
        }
    }
    
    /**
     *
     */
    public function listServers(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        
        //return $this->app->json($reply);
        
        return $this->app->render("pages/servers/list.twig", $variables);
    }
    
    /**
     *
     */
    public function getServer(Request $request)
    {
        $response = $this->makeSubquery($request);
        $reply = json_decode($response->getContent());
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        $variables["mode"] = "view";
        
        return $this->app->render("pages/servers/retrieve.twig", $variables);
    }
    
    /**
     *
     */
    public function getUpdatePage(Request $request, $serverId)
    {
        $subRequest = Request::create(
            $this->url("getServer", ["serverId" => $serverId]),
            "GET"
        );
        $subRequest->headers->set("Accept", "application/vnd.collection+json");

        $response = $this->app->handle($subRequest);
        $reply = json_decode($response->getContent());
        
        $collection = $reply->collection;
        
        /**
         * We are already on the edit page, but the standard retrieve results
         * contain a query to the edit page we just unset that here and pretend
         * like it was never there to begin with.
         */
        foreach ($collection->queries as $key => $query) {
            if ($query->rel == "edit-form") {
                unset($collection->queries[$key]);
            }
        }
        
        $variables = $this->templateVars();
        $variables["data"] = $reply->collection;
        $variables["mode"] = "edit";
        
        return $this->app->render("pages/servers/retrieve.twig", $variables);
    }
    
    /**
     *
     */
    public function updateServer(Request $request, $serverId)
    {
        $subRequest = $this->generateWriteSubrequest(
            $request,
            $this->url("updateServer", ["serverId" => $serverId]),
            "PATCH"
        );
        $result = $this->app->handle($subRequest);
        
        $variables = $this->templateVars();
        
        switch ($result->getStatusCode()) {
            case 200:
                return $this->app->redirect($this->url("listServers"));
                break;
            case 409:
                $variables["message"] = [
                    "type" => "error",
                    "content" => "A server already exists by that name, please try a unique name."
                ];
                return $this->app->render("pages/error.twig", $variables);
                break;
            default:
                $variables["message"] = [
                    "type" => "error",
                    "content" => "There was an error while processing your request."
                ];
                return $this->app->render("pages/error.twig", $variables);
                break;
        }
    }
    
    /**
     *
     */
    public function deleteServer(Request $request)
    {
        $delRequest = Request::create($request->getUri(), "DELETE");
        $delRequest->headers->set("Accept", "application/vnd.collection+json");
        
        $result = $this->app->handle($delRequest);
        
        if ($result->getStatusCode() == 204) {
            return $this->app->redirect($this->url("listServers"));
        } else {
            $variables = $this->templateVars();
            $variables["message"] = [
                "type" => "error",
                "content" => "The requested item was not found"
            ];
            
            return $this->app->render("pages/error.twig", $variables);
        }
        
    }
    
    /**
     *
     */
    private function generateWriteSubrequest(Request $request, $url, $method = "PUT")
    {
        $subRequest = Request::create(
            $url,
            $method,
            [],
            [],
            [],
            [],
            $this->generateJson($request)
        );
        
        $subRequest->headers->set(
            "Accept",
            "application/vnd.collection+json",
            true
        );
        
        $subRequest->headers->set(
            "HTTP_ACCCEPT",
            "application/vnd.collection+json",
            true
        );
        
        return $subRequest;
    }
    
    /**
     *
     */
    private function generateJson(Request $request)
    {
        $model = new ServerModel();
        $post = new \stdClass();
        $template = new \stdClass();
        $data = [];
        foreach ($model->toArray() as $field => $value) {
            if (strlen($request->get($field))) {
                $postField = new \stdClass();
                $postField->name = $field;
                $postField->value = $request->get($field);
                $data[] = $postField;
            }
        }
        $template->data = $data;
        $post->template = $template;
        
        return json_encode($post);
    }
}
