<?php
namespace oxide\http;
use oxide\http\Context;
use oxide\http\Router;
use oxide\http\Dispatcher;
use oxide\util\EventNotifier;

/**
 * Front Controller.  
 * 
 * tunnel for all user requests
 * Uses Router and Dispatcher objects to route the request to proper Controller
 * 
 * FrontController is also responsible for final output using Response object.
 * @package oxide
 * @subpackage http
 */
class FrontController {
   use \oxide\base\pattern\SharedInstanceTrait;
   const
      EVENT_START = 'FrontControllerStart',
      EVENT_END = 'FrontControllerEnd',
      EVENT_PRE_ROUTE = 'FrontControllerPreRoute',
      EVENT_POST_ROUTE = 'FrontControllerPostRoute',
      EVENT_PRE_DISPTACH = 'FrontControllerPreDispatch',
      EVENT_POST_DISPATCH = 'FrontControllerPostDispatch',
      EVENT_EXCEPTION = 'FrontControllerException';
   
	protected
      $_context = null,
		$_router = null,
		$_dispatcher = null;
   
   /**
    * Initializes the front controller
    * @param Context $context
    */
   public function __construct(Context $context) {
      $this->_context = $context;
   }
   
   /**
    * Get the application context
    * @return Context
    */
   public function getContext() {
      return $this->_context;
   }
   
   /**
    * Sets the router for the front controller
    * 
    * @param Router $router
    */
   public function setRouter(Router $router) {
      $this->_router = $router;
   }
   
   /**
    * Get the router for the front controller
    * @return Router
    */
	public function getRouter() {
		if($this->_router === null) {
			$this->_router = new Router();
		}
		
		return $this->_router;
	}
	
	
   /**
    * Execute method, implementing the Command pattern
    * 
    * Applicaton's main starting point.
    * Handles the current request, routes and dispatches to controller
    * @param \oxide\http\Context $context
    * @throws \oxide\http\Exception
    */
   public function run() {   
      $context = $this->getContext();
      $request = $context->getRequest();
		$response = $context->getResponse();
		$notifier = EventNotifier::sharedInstance();
      try {
         $notifier->notify(self::EVENT_START, $this);
         
         // get the routing object
         $router = $this->getRouter();
			$context->set('router', $router);
         $notifier->notify(self::EVENT_PRE_ROUTE, $this, $router,  $request);      
         $route = $router->route($request);
         $notifier->notify(self::EVENT_POST_ROUTE, $this, $router,  $route);      
         
         if(!$route) {
            throw new exception\HttpException("Unable to route requested path: {$request->getUrl()}");
         }
         
         // dispatch using the routing information
         $notifier->notify(self::EVENT_PRE_DISPTACH, $this, $route);
         if(!$route->completed) {
            $this->dispatch($route,$context);
         }
         $notifier->notify(self::EVENT_POST_DISPATCH, $this, $route);
      }
      
      catch(\Exception $exception) {
         $notifier->notify(self::EVENT_EXCEPTION, $this, $exception);      
         print '<pre>';
         throw $exception;
      }

		finally {
         // send the request back to client
         // at this point response should have body content intended to display
         $response->send(false);
         $notifier->notify(self::EVENT_END, $this);
		}
   }
   
   
   /**
    * Dispatches the $route to command for executing
    * 
    * @param \oxide\http\Route $route
    * @throws exception\HttpException
    */
   public function dispatch(Route $route) {
      $context = $this->getContext();
      
      // attempt to create the command using the route
      $factory = new CommandFactory();
      $command = $factory->create($route);
      
      // if unable to find the controller
      // attempt to reroute to the default controller
		if(!$command) {
        $router = $this->getRouter();  
        if($router) {
           $router->rerouteToDefaultController($route);
           // try again to crate command using new route info
           $command = $factory->create($route);
        }
		}
      
      // if controller failed to load
		// we will throw exception
		if(!$command) {
			// requested module's controller file was not found
			// this is basically an internal error.
		  	throw new exception\HttpException("Unable dispatch. [Module: '{$route->module}', "
         . "Controller: '{$route->controller}', Directory: '{$route->dir}']", 500);
      }
      
      if(!$command instanceof Command) {
         throw new exception\HttpException("Command is not instace of Command");
      }
      
      // finally dispatching
      $command->execute($context);
   }
}