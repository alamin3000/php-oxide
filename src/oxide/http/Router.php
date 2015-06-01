<?php
namespace oxide\http;
use oxide\base\Container;

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
class Router {
   public 
		$defaultModule = 'home',
		$defaultController = 'default',
      $indexFile = 'index.php',
		$defaultAction = '';
	
	protected
      $_config = null,
      $_registry = [],
      $_routes = [],
		$_schema = ['module', 'controller', 'action', 'params'],
		$_separator = '/';
   
	const
		MODULE      = 'module',
		CONTROLLER  = 'controller',
		ACTION      = 'action',
		PARAMS      = 'params';
   
   public function __construct(Container $config = null) {
      if($config)
         $this->_config = $config;
   }
   
   /**
    * Register given path with the module
    * 
    * @param string $path
    * @param string $module
    */
   public function register($module, $dir, $namespace = null) {
      $this->_registry[$module] = [$dir, $namespace];
   }
   
   /**
    * Unregister the given path from the registray
    * @param string $path
    */
   public function unregister($path) {
      unset($this->_registry[$path]);
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
	public function urlToRoute($path) {
      // some sanitizing
      // this needs to be done for some servers, specially with FastCGI enabled
      $url = str_replace('//', '/', str_replace($this->indexFile, '', $path));
      
      $route = new Route();
		$schema = $this->_schema;
		$parsed = \parse_url($url);
		$parts = explode($this->_separator, trim($parsed['path'], '/'));
		
		$imodule			= \array_search(self::MODULE, $schema);
		$icontroller	= \array_search(self::CONTROLLER, $schema);
		$iaction			= \array_search(self::ACTION, $schema);
		$iparam			= \array_search(self::PARAMS, $schema);

		$route->module = (($imodule !== FALSE) 
              && isset($parts[$imodule]) 
              && !empty($parts[$imodule]))
				  ? $parts[$imodule] : $this->defaultModule;
		$route->controller = (($icontroller !== FALSE) 
              && isset($parts[$icontroller]) 
              && !empty($parts[$icontroller]))
				  ? $parts[$icontroller] : $this->defaultController;
		$route->action = (($iaction !== FALSE) 
              && isset($parts[$iaction]) 
              && !empty($parts[$iaction]))
				  ? $parts[$iaction] : $this->defaultAction;
		$route->params = (($iparam !== FALSE) 
              && isset($parts[$iparam]) 
              && !is_null($parts[$iparam]))
				  ? \array_slice($parts, $iparam) : array();

      $route->path = $path;
		return $route;
	}
	
	
	/**
	 * getPathRelativeToBase function.
	 * 
	 * @access public
	 * @param mixed $request
	 * @return void
	 */
	function getPathRelativeToBase($request) {
		$path = $request->getUriComponents(Request::URI_PATH);
      $base = '/'. trim($request->getBase(), '/') . '/';
      
      if (substr($path, 0, strlen($base)) == $base) {
			$str = '/'. substr($path, strlen($base));
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
   public function match(Route $route) {
      $registry = $this->_registry;
      if(empty($registry)) return FALSE;
      $module = $route->module;
      
      if(!isset($registry[$module])) return FALSE;
      $moduleinfo = $registry[$module];
      list($dir, $namespace) = $moduleinfo;
      $route->namespace = $namespace;
      $route->dir = $dir;
      
      return TRUE;
   }
   
   /**
    * Routes the given $request object into Route object
    * 
    * If unable to route, then NULL will be sent
    * @param \oxide\http\Request $request
    * @return Route|null
    */
   public function route(Request $request) {
      $path = $this->getPathRelativeToBase($request);
      $route = $this->urlToRoute($path);
      if($this->match($route)) {
         $route->method = $request->getMethod();
         return $route;
      } else {
         return NULL;
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
   public function rerouteToDefaultController(Route $route) {
      /*
       * we will also shift the routing parts to make space for the default module name
       */
      array_unshift($route->params, $route->action); 	# glue the action part as param
      $route->action = $route->controller;				# set controller as action
      $route->controller = $this->defaultController;# using default controller

      // unshift may create an empty param
      // we need to remove that
      if(is_array($route->params) &&  isset($route->params[0]) && empty($route->params[0])) {
         $route->params = [];
      }
   }
   
   /**
    * Reroutes the given $route into default action
    * 
    * Meaning:
    *    - the current action, if any will be shifted as first param
    *    - action name will be current $defaultAction name of the router
    *    - everything else is unchanged.
    * @param \oxide\http\Route $route
    */
   public function rerouteToDefaultAction(Route $route) {
      array_unshift($route->params, $route->action); 	# glue the action part as param
      $route->action = $this->defaultAction;
      
      // unshift may create an empty param
      // we need to remove that
      if(is_array($route->params) &&  isset($route->params[0]) && empty($route->params[0])) {
         $route->params = [];
      }
   }
}