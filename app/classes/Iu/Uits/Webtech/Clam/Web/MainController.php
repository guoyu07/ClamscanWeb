<?php
/**
 *
 */
namespace Iu\Uits\Webtech\Clam\Web;

/**
 *
 */
class MainController
{
    /** @var object The silex application */
    private $app;
    
    /**
     * Magic Construct Function
     *
     * @param object $app The silex application itself
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     *
     */
    public function showEnqueuePage()
    {
        return $this->app["twig"]->render(
            "pages/index.twig",
            $this->app["options"]
        );
    }
}
