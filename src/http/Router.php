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
      $_routes = [],
		$_schema = ['module', 'controller', 'action', 'params'],
		$_separator = '/';
   
	const
		MODULE      = 'module',
		CONTROLLER  = 'controller',
		ACTION      = 'action',
		PARAMS      = 'params';

	/**
	 * sets URL routing part separator
	 *
	 * This separator is used to break down the routing parts from URL string
	 * defaults is set to standard '/' path character.
	 * @param string $separator
	 */
	public function setUrlRoutingPartSeparator($separator)
	{
		$this->_separator = $separator;
	}

	/**
	 * sets URL routing schema
	 *
	 * URL routing schema is defined by an array with linear routing parts.
	 * Each parts are separated by current URL parts separator
	 *
	 * For example, standard routing for URL is based on following schema:<br>
	 * /module_name/controller_name/action_name/param1/param2...<br>
	 * For this schmea, defination array will be:
	 * <code>
	 *		$schema = array('module', 'controller', 'action', 'params')
	 * </code>
	 *
	 *
	 * @param array $schema
	 */
	public function setUrlRoutingSchemaDefination($schema)
	{
		$this->_schema = $schema;
	}
   
   public function register($path, $route) {
      
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
    * add a forward routing condition
    * 
    * @param type $target
    * @param type $forward 
    */
   public function addRouteMap($target, $forward) {
      $this->_routeMap[(string) $target] = $forward;
   }
   
   /**
    *
    * @param type $target
    * @param type $forward 
    */
   public function addUrlMap($url, $forward) {
      $this->_routeMap[$url] = $forward;
   }
   
   /**
    * match if any URL rewrite has been configured
    * if so, matched path will be returned
    * 
    * @param string $path 
    * @return string
    */
   protected function _matchRedirect($path)
   {
      $tmp_path = trim(strtolower($path), '/');
      $reroutes = $this->_context->getConfig()->redirect;
      
      if($reroutes) {
         foreach( $reroutes as $target => $forward) {
            if($tmp_path == trim(strtolower($target), '/')) {
               return $forward;
            }
         }
      }
      
      
      return $path;
   }
   
   
   /**
    *
    * @param Route $route1
    * @param Route $route2
    * @return type 
    */
   protected function _matchReRoute(Route $route1, Route $route2)
   {
      if($route1->module == $route2->module ||
         $route1->module == '*') {
         // module match
      }
      
      if($route1->controller == '*' ||
         $route1->controller == $route2->controller) {
         // controller match
      }
      
      if($route1->action == '*' ||
         $route2->action == $route2->action) {
         // action match
      }
      
      return $route;
   }
   

   /**
	 * match if defined routes are available with given path.
	 *
	 * @access protected
	 * @param $route route to be match
	 * @return Route
	 * @todo complete implement this method
	 */
	protected function match(Route $route)
	{
//      // match using config file
//      $path = $this->_matchRedirect($route->path);
//      if($path != $route->path) {
//         header ('HTTP/1.1 301 Moved Permanently');
//         header ('Location: '. $path);
//         exit;
//      }
//      
      
		return $route;
	}
   
   /**
    * Route the given regquest
    * 
    * @param \oxide\http\Request $request
    * @return Router
    */
   public function routeRequest(Request $request) {
      $path = $request->getUriComponents(Request::URI_PATH);
      $route = $this->routeUrlPath($path);
      $route->method = $request->getMethod();
      
      return $route;
   }
   
	

	/**
	 * route given url
	 * 
	 * @param string $path
	 * @return Route
	 */
	public function routeUrlPath($path) {
		// parse the path for routing components
		$route = $this->urlToRoute($path);
		
		// match the route with predefined routings
		return $this->match($route);
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