<?php
namespace oxide\http;
use oxide\validation\misc\VariableNameValidator;
use oxide\base\Container;


/**
 * Command Controller
 * 
 * implements Command to comply with front controller engines specification
 * provides command execution life cycle events
 * 
 * @package oxide
 * @subpackage http
 */
abstract class CommandFactory {
   public 
      $classNamespace = 'controller',
      $classSuffix = 'Controller',
      $classPrefix = null;

   protected
      $_config = null;
   
   /**
    * Generate a full resolvable class name based on route
    * 
    * @param \oxide\http\Route $route
    * @return null
    */
   public static function generateClassName(Route $route) {
      $validator = new VariableNameValidator();
      $namespace = $route->namespace;
      $controller = self::stringToName($route->controller);
      
      // make sure module and controller names are provided and are valid
      if(empty($controller) || !$validator->validate($controller)) return null;
      
      $classnamespace = self::$classNamespace;
      $classsuffix = self::$classSuffix;
      $classprefix = self::$classPrefix;
      $class = "{$namespace}\\{$classnamespace}\\{$classprefix}{$controller}{$classsuffix}";
      return $class;
   }
   
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null|\oxide\http\class
    */
   public static function create(Route $route, Container $config = null) {
      $class = self::generateClassName($route);
      if($class) {
         $instance = new $class($route, $config);
      } else {
         $instance = null;
      }

		return $instance;
   }

   
   /**
    * Convert string to a controller/action name
    * @param type $string
    * @return type
    */
   public static function stringToName($string) {
      return
         str_replace(' ', '', ucwords(str_replace(['-','_'], ' ', $string)));
   }
}