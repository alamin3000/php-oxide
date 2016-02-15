<?php
namespace Oxide\Http;

use Oxide\Support\Pattern\SharedInstanceTrait;
use Oxide\Http\Exception\HttpException;
use Oxide\Support\EventNotifier;


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
class FrontController
{
    use SharedInstanceTrait;
    const
        EVENT_START = 'FrontControllerStart',
        EVENT_END = 'FrontControllerEnd',
        EVENT_PRE_ROUTE = 'FrontControllerPreRoute',
        EVENT_POST_ROUTE = 'FrontControllerPostRoute',
        EVENT_PRE_DISPTACH = 'FrontControllerPreDispatch',
        EVENT_POST_DISPATCH = 'FrontControllerPostDispatch',
        EVENT_EXCEPTION = 'FrontControllerException';

    protected
        $routes = [],
        $context = null;


    /**
     * Initializes the front controller
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get the application context
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->context->resolve('Oxide\Http\Router');
    }

    /**
     * Execute method, implementing the Command pattern
     *
     * Applicaton's main starting point.
     * Handles the current request, routes and dispatches to controller
     * @throws \Exception
     * @internal param Context $context
     */
    public function run()
    {
        $context = $this->getContext();
        $request = $context->get('request');
        $response = $context->get('response');
        $notifier = EventNotifier::sharedInstance();

        try {
            $notifier->notify(self::EVENT_START, $this);

            // get the routing object
            $router = $this->getRouter();

            $notifier->notify(self::EVENT_PRE_ROUTE, $this, $router, $request);
            $route = $router->route($request);
            $notifier->notify(self::EVENT_POST_ROUTE, $this, $router, $route);

            if (!$route) {
                throw new HttpException("Unable to route requested path: {$request->getUrl()}");
            }

            // dispatch using the routing information
            $context['route'] = $route; // store the route in the context
            $notifier->notify(self::EVENT_PRE_DISPTACH, $this, $route);
            if (!$route->completed) {
                $this->dispatch($route, $context);
            }
            $notifier->notify(self::EVENT_POST_DISPATCH, $this, $route);
        } catch (\Exception $exception) {
            $notifier->notify(self::EVENT_EXCEPTION, $this, $exception);
            print '<pre>';
            throw $exception;
        } finally {
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
    public function dispatch(Route $route)
    {
        $context = $this->context;

        // attempt to create the command using the route
        $factory = $context->resolve('\Oxide\Http\CommandFactory');
        $command = $factory->create($route);

        // if unable to find the controller
        // attempt to reroute to the default controller
        if (!$command) {
            $router = $this->getRouter();
            if ($router) {
                $router->rerouteToDefaultController($route);
                // try again to crate command using new route info
                $command = $factory->create($route);
            }
        }

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

        // finally dispatching
        $command->execute($context);
    }
}