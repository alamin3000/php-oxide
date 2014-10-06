<?php
namespace oxide\http;

class CommandFactory {
   public static 
      $classSuffix = 'Controller',
      $classPrefix = null;
           
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null
    */
   public static function generateClassName(Route $route) {
      $validator = new VariableNameValidator();
      
      // validate module name
      if(!$validator->validate($route->module)) {
         return null;
      }
      
      // validate controller name if given
      if(!empty($route->controller) && !$validator->validate($route->controller)) {
         return null;
      }
      
		$module = $route->module;
      $controller = ucwords($route->controller);
      $class = "{$module}\controller\\{$controller}Controller";
      return $class;
   }
   
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null|\oxide\http\class
    * @throws Exception
    */
   public static function createWithRoute(Route $route) {
      $class = self::generateClassName($route);
      if($class) {
         $instance = new $class($route);
      } else {
         $instance = null;
      }

		return $instance;
   }
}