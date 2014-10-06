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
class FrontController implements Command {
   use \oxide\util\pattern\DefaultInstanceTrait;
   const
      EVENT_START = 'FrontControllerStart',
      EVENT_END = 'FrontControllerEnd',
      EVENT_PRE_ROUTE = 'FrontControllerPreRoute',
      EVENT_POST_ROUTE = 'FrontControllerPostRoute',
      EVENT_PRE_DISPTACH = 'FrontControllerPreDispatch',
      EVENT_POST_DISPATCH = 'FrontControllerPostDispatch',
      EVENT_EXCEPTION = 'FrontControllerException';
   
	protected
		$_router = null,
		$_dispatcher = null;
				
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
   public function execute(Context $context) {      
      $request = $context->getRequest();
		$response = $context->getResponse();
		$notifier = EventNotifier::defaultInstance();
      try {
         $notifier->notify(self::EVENT_START, $this);
         
         // get the routing object
         $router = $this->getRouter();
			$context->set('router', $router);
         $notifier->notify(self::EVENT_PRE_ROUTE, $this, $router,  $request);      
         $route = $router->routeRequest($request);
         $notifier->notify(self::EVENT_POST_ROUTE, $this, $router,  $route);      
         
         // dispatch using the routing information
         $dispatcher = $this->getDispatcher();
         $context->set('dispatcher', $dispatcher);
         $notifier->notify(self::EVENT_PRE_DISPTACH, $this, $dispatcher, $route);
         $dispatcher->addRouteToQueue($route);
         $dispatcher->startDispatchingQueue($context);
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