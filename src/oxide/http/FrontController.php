<?php
namespace oxide\http;
use oxide\http\Context;
use oxide\http\Router;
use oxide\http\Dispatcher;
use oxide\util\EventNotifier;
use oxide\util\pattern\DefaultInstanceTrait;
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
   use DefaultInstanceTrait;
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
    * Get the context
    * @return Context
    */
   public function getContext() {
      return $this->_context;
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
	
	public function getDispatcher() {
		if($this->_dispatcher === null) {
			$this->_dispatcher = new Dispatcher();
		}
		
		return $this->_dispatcher;
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
		$notifier = EventNotifier::defaultInstance();
      try {
         $notifier->notify(self::EVENT_START, $this);
         
         // get the routing object
         $router = $this->getRouter();
			$context->set('router', $router);
         $notifier->notify(self::EVENT_PRE_ROUTE, $this, $router,  $request);      
         $route = $router->route($request);
         $notifier->notify(self::EVENT_POST_ROUTE, $this, $router,  $route);      
         
         if(!$route) {
            throw new \Exception("Unable to route requested path: {$request->getPath()}");
         }
         
         // dispatch using the routing information
         $dispatcher = $this->getDispatcher();
         $context->set('dispatcher', $dispatcher);
         $notifier->notify(self::EVENT_PRE_DISPTACH, $this, $dispatcher, $route);
         $dispatcher->dispatch($route,$context);
         $notifier->notify(self::EVENT_POST_DISPATCH, $this, $dispatcher, $route);
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
}