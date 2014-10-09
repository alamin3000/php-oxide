<?php
namespace oxide\http;

/**
 * Router class.
 *
 * Primary objective of this class is to route given request using predefined routing schema.
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
		$defaultAction = null;
	
	protected
      $_registry = [],
      $_routes = [],
		$_schema = ['module', 'controller', 'action', 'params'],
		$_separator = '/';
   
	const
		MODULE      = 'module',
		CONTROLLER  = 'controller',
		ACTION      = 'action',
		PARAMS      = 'params';
   
   /**
    * Register given path with the module
    * 
    * @param string $path
    * @param string $module
    */
   public function register($path, $module) {
      $this->_registry[$path] = $module;
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
	public function urlToRoute($url) {
      // some sanitizing
      // this needs to be done for some servers, specially with FastCGI enabled
      $url = str_replace($this->indexFile, '', $url);
      $url = str_replace('//', '/', $url);
      
      $route = new Route();
      $route->path = $url;
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

		return $route;
	}
 
   /**
	 * match if defined routes are available with given path.
	 *
	 * @access protected
	 * @param $route route to be match
	 * @return bool
	 * @todo complete implement this method
	 */
	protected function match($path) {
      $registry = $this->_registry;
      if(empty($registry)) return FALSE;
            
      // sort with longer path first
      $offsets = array_keys($registry);
      rsort($offsets);
      
      foreach($offsets as $offset) {
         $module = $registry[$offset];
         if(($path == $offset) || ($offset === "" || strpos($path, rtrim($offset, '/') . '/') === 0)) {
            // path matched with registry
            $parampath = trim(substr($path, strlen($offset)), '/');
            return $parampath;
         }
      }
      
		return FALSE;
	}
   
   public function route(Request $request) {
      $registry = $this->_registry;
      if(empty($registry)) return NULL;
      
      $route = $this->urlToRoute($request->getPath());
      $path = $route->module;
      if(!isset($registry[$path])) return NULL;
      $route->method = $request->getMethod();
      return $route;
   }
	   
   /**
    * makes necessary changes to the route object and reroutes to default controller
    * 
    * This is following effect on router
    * 1) module remains same
    * 2) controller becomes default
    * 3) action takes value of controller
    * 4) params 
    * @param \oxide\http\Route $route
    */
   public function rerouteToDefaultController(Route $route)
   {
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
   
   public function rerouteToDefaultAction(Route $route)
   {
      array_unshift($route->params, $route->action); 	# glue the action part as param
      $route->action = $this->defaultAction;
      
      // unshift may create an empty param
      // we need to remove that
      if(is_array($route->params) &&  isset($route->params[0]) && empty($route->params[0])) {
         $route->params = [];
      }
   }
}