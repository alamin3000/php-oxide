<?php
namespace Oxide\Http;

use Oxide\Common\Pattern\SharedInstanceTrait;
use Oxide\Http\Exception\HttpException;

/**
 * dispatcher
 *
 *    dispatches request to proper module controller
 * provides dispatching looping machanism.   To start dispatching loop,
 * use addRouteToQueue() to add request and startDispatchingQueue() to start the dispatch
 *
 * @package oxide
 * @subpackage http
 */
class Dispatcher
{
    use SharedInstanceTrait;

    protected
        $_context = null;

    public function __construct(Context $context)
    {
        $this->_context = $context;
    }

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
     *
     * @param Route $route route where to dispatch
     * @throws HttpException
     * @internal param Context $context context which to dispatch
     * @notifies DispatcherPreDispatch
     * @notifies DispatcherPostDispatch
     */
    public function dispatch(Route $route)
    {
        $context = $this->_context;
        $factory = new CommandFactory();
        $command = $factory->create($route);

        // if controller is not loaded, usaully means controller does not exits
        // then we will attempt to send to default controller and adjust the Route
//		if(!$command) {
//        $router = $context->get('router');  
//        if($router) {
//           $router->rerouteToDefaultController($route);
//           // try again to crate command using new route info
//           $command = $factory->create($route);
//        }
//		}

        // if controller failed to load
        // we will throw exception
        if (!$command) {
            // requested module's controller file was not found
            // this is basically an internal error.
            throw new HttpException("Unable dispatch. [Module: '{$route->module}', "
                . "Controller: '{$route->controller}', Directory: '{$route->dir}']", 500);
        }

        if (!$command instanceof Command) {
            throw new HttpException("Command is not instace of Command");
        }

        $command->execute($context);
    }
}