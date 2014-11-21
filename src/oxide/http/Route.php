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
       * namespace for the module.
       * @var string 
       */
      $namespace = null,

           
      /**
       * Directory path for the module.
       * This is useful to access the module directory in the various controllers.
       * Template controller relies on this to access view scripts.
       * @var string
       */
      $dir = null,
           
           
		/**
		 * array of param strings
		 * @var array
		 */
		$params = [];

	/**
	 * construct a new empty Route object
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