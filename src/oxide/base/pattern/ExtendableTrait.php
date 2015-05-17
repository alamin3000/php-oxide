<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base\pattern;

trait ExtendableTrait {
   protected
   	$_t_extendedObjects = [],
      $_t_callables = [];
   
   public function hasExtendedClass($object, $strict = false) {
	   if(isset($this->_t_extendedObjects[get_class($object)])) {
		   if($strict) {
			   $obj = $this->_t_extendedObjects[get_class($object)];
			   if($obj ===  $object) return true;
			   else return false;
		   } else {
			   return true;
		   }
	   } 
	   
	   return false;
   }
   
   public function hasExtendedMethod($name) {
	   return (isset($this->_t_callables[$name]));
   }
      
   /**
    * extendObject function.
    * 
    * @access public
    * @param mixed $object
    * @return void
    */
   public function extendObject($object, $prefix = '') {
	   if(!is_object($object)) throw new \Exception("Not object.");
      $reflector = new \ReflectionClass($object);
      $this->_t_extendedObjects[$reflector->getName()] = $object;
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
	      $name = $prefix . $method->getName();
	      $this->extendCallable($name, [$object, $name]);
      }
   }
   
   public function extendClass($classname, $instance, $prefix = '') {
	   $reflector = new \ReflectionClass($object);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
	      $name = $prefix . $method->getName();
	      $this->extendCallable($name, [$instance, $name]);
      }
   }
   
   /**
    * extendClosure function.
    * 
    * @access public
    * @param mixed $name
    * @param \Closure $closure
    * @return void
    */
   public function extendClosure($name, \Closure $closure, $overide = false) {
      $this->extendCallable($name, $closure, $overide);
   }
   
   /**
    * extendCallable function.
    * 
    * @access public
    * @param mixed $name
    * @param callable $callable
    * @return void
    */
   public function extendCallable($name, callable $callable, $override = false) {
	   if(isset($this->_t_callables[$name])) {
		   if(!$override) { 
			   throw new \Exception("Method {$name} already exists.");
			}
		}
			
	   $this->_t_callables[$name] = $callable;
   }
   
   /**
    * callExtendedMethod function.
    * 
    * @access public
    * @param mixed $name
    * @param array $args (default: null)
    * @return void
    */
   public function invokeExtended($name, array $args = null) {
      if(isset($this->_t_callables[$name])) {
         $callable = $this->_t_callables[$name];
			return call_user_func_array($callable, $args);         
      }
   }   
}