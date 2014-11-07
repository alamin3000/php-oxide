<?php
namespace oxide\http;
use oxide\validation\misc\VariableNameValidator;


class CommandFactory {
   public static 
      $defaultController = 'default',
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
      
      if(empty($route->controller)) {
         $route->controller = self::$defaultController;
      }
      
		$module = $route->module;
      $namespace = $route->namespace;
      $controller = ucwords($route->controller);
      $class = "{$namespace}\controller\\{$controller}Controller";
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