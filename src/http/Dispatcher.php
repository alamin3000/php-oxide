<?php
namespace oxide\http;
use oxide\Loader;
use oxide\util\EventNotifier;
use Exception;

/**
 * dispatcher
 *
 *	dispatches request to proper module controller
 * provides dispatching looping machanism.   To start dispatching loop, use addRouteToQueue() to add request and startDispatchingQueue() to start the dispatch
 * 
 * @package oxide
 * @subpackage http
 */
class Dispatcher {
   use \oxide\util\pattern\DefaultInstanceTrait;
	protected
		$_routes = array(),
		$_modules  = array(),
		$_default = array(),
		$_controllerInstance = '\oxide\http\Command';


	/**
	 * adds the given route to dispatching queue
	 * 
	 * @return 
	 * @param Route $route
	 */
	public function addRouteToQueue(Route $route) {
		$this->_routes[] = $route;
	}
		
	/**
	 * starts dispatching loop
	 * 
	 * @access public
	 * @param Context $context context which to dispatch
	 */
	public function startDispatchingQueue(Context $context) {
		// make sure there is routes to dispatch to
		if(count($this->_routes) == 0) return;
		
		// starts the dispatching loop
		$i = 0;
		do {
			$route = $this->_routes[$i];
         $this->shouldRetryDispatching = true;
			$this->dispatch($route, $context);
			$i++;	
		} while(isset($this->_routes[$i]));
	}
	
	/**
	 * dispatch given $context  to the controller via given $route
	 * 
	 * this method does not uses route queue, instead immediately dispatches given route
	 * to queue a route for next dispatch, use addRouteToQueue() method
	 *  
	 * Dispatching Rule: when controller is not found:
	 * if given $route->controller is not found, then it will attempt to load Router::defaultController.
	 * In that case, method will adjust the routing component - set the controller name to Router::defaultController,
	 * action name to the $route->controller and $route->action will be prepanded to the $route->params array
	 * 
	 * 
	 * @return 
	 * @param Route $route route where to dispatch
	 * @param Context $context context which to dispatch
    * @notifies DispatcherPreDispatch
    * @notifies DispatcherPostDispatch
	 */
	public function dispatch(Route $route, Context $context) {
		// retrive the routed module and action.
      $notifier = EventNotifier::defaultInstance();
      $notifier->notify('DispatcherDispatch', $this, array('route' => $route));

      // we should update the route's directory info there
      if(empty($route->dir)) {
         if(isset(Loader::$namespaces[$route->module])) { $route->dir = Loader::$namespaces[$route->module]; }
      }
      
		$context->route = $route;
		$command = CommandController::createWithRoute($route);
      
		// if controller is not loaded, usaully means controller does not exits
		// then we will attempt to send to default controller and adjust the Route
		if(!$command) {
         $router = $context->router;      
         $router->rerouteToDefaultController($route);
         // try again to crate command using new route info
			$command = CommandController::createWithRoute($route);
		}
		
		// if controller failed to load
		// we will throw exception
		if(!$command) {
			// requested module's controller file was not found
			// this is basically an internal error.
		  	throw new Exception("Unable dispatch. [Module: '{$route->module}', Controller: '{$route->controller}', Directory: '{$route->dir}']", 500);
      }
      
      if(!$command instanceof $this->_controllerInstance) {
         throw new Exception("Command is not instace of Command");
      }
		
		// finally executing the http request command
      $context->set('controller', $command);
		$command->execute($context);
	}
}