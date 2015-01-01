<?php
namespace oxide\http;
use oxide\util\EventNotifier;

/**
 * dispatcher
 *
 *	dispatches request to proper module controller
 * provides dispatching looping machanism.   To start dispatching loop, 
 * use addRouteToQueue() to add request and startDispatchingQueue() to start the dispatch
 * 
 * @package oxide
 * @subpackage http
 */
class Dispatcher {
	/**
	 * dispatch given $context  to the controller via given $route
	 * 
	 * this method does not uses route queue, instead immediately dispatches given route
	 * to queue a route for next dispatch, use addRouteToQueue() method
	 *  
	 * Dispatching Rule: when controller is not found:
	 * if given $route->controller is not found, 
    * then it will attempt to load Router::defaultController.
	 * In that case, method will adjust the routing component 
    *    - set the controller name to Router::defaultController,
	 * action name to the $route->controller and $route->action will be 
    * prepanded to the $route->params array
	 * 
	 * @return 
	 * @param Route $route route where to dispatch
	 * @param Context $context context which to dispatch
    * @notifies DispatcherPreDispatch
    * @notifies DispatcherPostDispatch
	 */
	public function dispatch(Route $route, Context $context) {
		// retrive the routed module and action.
      $notifier = EventNotifier::sharedInstance();
      $notifier->notify('DispatcherDispatch', $this, ['route' => $route]);

		$context->route = $route;
		$command = CommandFactory::createWithRoute($route);
      
		// if controller is not loaded, usaully means controller does not exits
		// then we will attempt to send to default controller and adjust the Route
//		if(!$command) {
//         $router = $context->router;  
//         if($router) {
//            $router->rerouteToDefaultController($route);
//            // try again to crate command using new route info
//            $command = CommandFactory::createWithRoute($route);
//         }
//		}
		
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

      $command->execute($context);
	}
}