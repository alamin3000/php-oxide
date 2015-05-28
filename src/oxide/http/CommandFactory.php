<?php
namespace oxide\http;
use oxide\validation\misc\VariableNameValidator;

/**
 * Command Controller
 * 
 * implements Command to comply with front controller engines specification
 * provides command execution life cycle events
 * 
 * @package oxide
 * @subpackage http
 */
class CommandFactory {
   public 
      $classNamespace = 'controller',
      $classSuffix = 'Controller',
      $classPrefix = null;

   protected
      $_config = null;
   
   public function __construct() {
      
   }
   
   /**
    * Generate a full resolvable class name based on route
    * 
    * @param \oxide\http\Route $route
    * @return null
    */
   public function generateClassName(Route $route) {
      $validator = new VariableNameValidator();
      $namespace = $route->namespace;
      $controller = self::stringToName($route->controller);
      
      // make sure module and controller names are provided and are valid
      if(empty($controller) || !$validator->validate($controller)) return null;
      
      $classnamespace = $this->classNamespace;
      $classsuffix = $this->classSuffix;
      $classprefix = $this->classPrefix;
      $class = "{$namespace}\\{$classnamespace}\\{$classprefix}{$controller}{$classsuffix}";
      return $class;
   }
   
   /**
    * 
    * @param \oxide\http\Route $route
    * @return null|\oxide\http\class
    */
   public function create(Route $route) {
	   $instance = null;
      $class = $this->generateClassName($route);
      if($class) {
	      if(class_exists($class)) {
	         $instance = new $class($route);
	      }
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