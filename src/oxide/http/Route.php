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
class Route
{
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
		 * exact module directory
		 *
		 * @var string
		 */
		$dir = null,

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
           
      $namespace = null,

		/**
		 * array of param strings
		 * @var array
		 */
		$params = array();

	/**
	 * construct a new Route object
	 * 
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @param mixed $params
	 * @param string $dir
	 */
	public function __construct($module = null, $controller = null, $action = null, $params = null, $dir = null)
	{
		$this->module = $module;
		$this->controller = $controller;
		$this->action = $action;
		$this->dir = $dir;

      if(!is_array($params)) $params = [$params];
		$this->params = $params;
	}
   
   public function __toString() {
      return $this->module . '/' . $this->controller . '/'. $this->action;
   }
}