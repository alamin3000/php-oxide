<?php
namespace oxide\http;

/**
 * Route class
 * 
 * This object simply holds routing information.
 * Usually it is a responsibility of the Router object to create an instance of Route class
 * 
 * @package oxide
 * @subpackage http
 */
class Route {
	public
      /**
       * @var string HTTP method for this route
       */
      $method = 'get',
           
      /**
       * actual raw path that this route represent
       * @var string
       */
      $path = null,
           
		/**
		 * module name
		 * @var string
		 */
		$module = null,

		/**
		 * controller name
		 * @var string
		 */
		$controller = null,

		/**
		 * action name
		 * @var string
		 */
		$action = null,
           
      /**
       * @var string namespace for the module
       */
      $namespace = null,

		/**
		 * array of param strings
		 * @var array
		 */
		$params = [];

	/**
	 * construct a new Route object
	 * 
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @param mixed $params
	 * @param string $dir
	 */
	public function __construct() {
	}
   
   public function __toString() {
      $path = $this->module . '/' . $this->controller . '/'. $this->action;
      if($this->params) {
         $path .= '/' . implode('/', $this->params);
      }
      
      return $path;
   }
}