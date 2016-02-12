<?php
namespace Oxide\Http;

/**
 * Router class.
 *
 * Primary objective of this class is to route given request using predefined routing schema.
 * Also mananages routes registry
 *
 * @package oxide
 * @subpackage http
 * @todo provide in class rerouting.  complete addRoute and match() functions
 */
class Router
{
    public
        $defaultModule = 'home',
        $defaultController = 'default',
        $indexFile = 'index.php',
        $defaultAction = 'index';

    protected
        $_config = null,
        $_registry = [],
        $_separator = '/';

    const
        MODULE = 'module',
        CONTROLLER = 'controller',
        ACTION = 'action',
        PARAMS = 'params';

    /**
     *
     * @param type $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->_config = $config;
        }
    }

    /**
     * Register given path with the module
     *
     * @param string $path
     * @param string $module
     */
    public function register($module, $dir, $namespace = null)
    {
        $this->_registry[$module] = [$dir, $namespace];
    }

    /**
     * Unregister the given path from the registray
     * @param string $path
     */
    public function unregister($path)
    {
        unset($this->_registry[$path]);
    }

    /**
     * Create a route based on given array
     *
     * @param array $arr
     * @return Route
     */
    public function arrayToRoute(array $arr)
    {
        $route = new Route();
        $route->module = isset($arr['module']) ? $arr['module'] : $this->defaultModule;
        $route->controller = isset($arr['controller']) ? $arr['controller'] : $this->defaultController;
        $route->action = isset($arr['action']) ? $arr['action'] : $this->defaultAction;
        $route->params = isset($arr['params']) ? (array)$arr['params'] : [];
        $route->path = isset($arr['path']) ? $arr['path'] : implode('/', array_values($arr));

        return $route;
    }

    /**
     * create route from given url path
     *
     * Uses the "path" information of the given URL and current URL routing schema to
     * create an Route object
     *
     * URL routing schema can be changed by setUrlRoutingSchemaDefination
     * @see setUrlRoutingSchemaDefination
     * @param string $url
     * @return Route
     */
    public function urlToRoute($path)
    {
        // some sanitizing
        // this needs to be done for some servers, specially with FastCGI enabled
        $url = str_replace('//', '/', str_replace($this->indexFile, '', $path));

        $route = new Route();
        $parsed = \parse_url($url);
        $parts = explode($this->_separator, trim($parsed['path'], '/'));

        $shiftParts = function ($default = null) use (&$parts) {
            return (isset($parts[0]) && !empty($parts[0])) ? array_shift($parts) : $default;
        };

        $route->module = $shiftParts($this->defaultModule);
        $route->controller = $shiftParts($this->defaultController);
        $route->action = $shiftParts($this->defaultAction);
        $route->params = array_filter($parts);

        $route->path = $path;
        return $route;
    }

    /**
     * getPathRelativeToBase function.
     *
     * @access public
     * @param Request $request
     * @return string
     */
    function getPathRelativeToBase(Request $request)
    {
        $path = $request->getUriComponents(Request::URI_PATH);
        $base = '/' . trim($request->getBase(), '/') . '/';

        if (substr($path, 0, strlen($base)) == $base) {
            $str = '/' . substr($path, strlen($base));
        } else {
            $str = $path;
        }

        return $str;
    }

    /**
     * Attempt to match the given route with current router registry
     *
     * If matched, it will update the namespace, dir of the route
     * @param Route $route
     * @return boolean
     */
    public function match(Route $route)
    {
        $registry = $this->_registry;
        if (empty($registry)) {
            return false;
        }
        $module = $route->module;

        if (!isset($registry[$module])) {
            return false;
        }
        $moduleinfo = $registry[$module];
        list($dir, $namespace) = $moduleinfo;
        $route->namespace = $namespace;
        $route->dir = $dir;

        return true;
    }

    /**
     * Routes the given $request object into Route object
     *
     * If unable to route, then NULL will be sent
     * @param \oxide\http\Request $request
     * @return Route|null
     */
    public function route(Request $request)
    {
        $path = $this->getPathRelativeToBase($request);
        $route = $this->urlToRoute($path);
        if ($this->match($route)) {
            $route->method = $request->getMethod();
            return $route;
        } else {
            return null;
        }
    }

    /**
     * makes necessary changes to the route object and reroutes to default controller
     *
     * This has following effect on router
     * 1) module remains same
     * 2) controller becomes default
     * 3) action takes value of controller
     * 4) params are updated to reflect the shifts
     * @param \oxide\http\Route $route
     */
    public function rerouteToDefaultController(Route $route)
    {
        $action = $route->action;
        if ($action && $action != $this->defaultAction) {
            array_unshift($route->params, $action);    # glue the action part as param
        } else {
            $route->action = $this->defaultAction;
        }

        $route->action = $route->controller;                # set controller as action
        $route->controller = $this->defaultController;  # using default controller

        // unshift may create an empty param
        // we need to remove that
        if (is_array($route->params) && isset($route->params[0]) && empty($route->params[0])) {
            $route->params = [];
        }
    }

    /**
     * Reroutes the given $route into default action
     *
     * Meaning:*+6
     *
     *    - the current action, if any will be shifted as first param
     *    - action name will be current $defaultAction name of the router
     *    - everything else is unchanged.
     * @param \oxide\http\Route $route
     */
    public function rerouteToDefaultAction(Route $route)
    {
        $action = $route->action;
        if ($action && $action != $this->defaultAction) {
            array_unshift($route->params, $action);    # glue the action part as param
        } else {
            $route->action = $this->defaultAction;
        }

        // unshift may create an empty param
        // we need to remove that
        if (is_array($route->params) && isset($route->params[0]) && empty($route->params[0])) {
            $route->params = [];
        }
    }
}