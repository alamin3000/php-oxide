<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\helper;

class _helper {
   
   static protected
      $_instance = null,
      $_invokers = [];
   
   private function __construct() {

   }
   
   /**
    * Get instance of helper class
    * 
    * @return
    */
   public static function instance() {
      if(self::$_instance === null) {
         self::$_instance = new static();
      }
      
      return self::$_instance;
   }
   
   /**
    * 
    * @param type $name
    * @param \Closure $function
    */
   public static function extendClosure($name, \Closure $function) {
      self::$_invokers[$name] = $function;
   }
   
   public static function extendClass($class) {
      $reflector = new \ReflectionClass($class);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
         $this->_t_methods[$method] = $object;
      }
   }
   
   public static function __callStatic($name, $arguments) {
      if(self::$_invokers[$name]) {
         $callable = self::$_invokers[$name];
         return call_user_func_array($callable, $arguments);
      } else {
         throw new \Exception('Unable to find helper method: '. $name);
      }
   }
}