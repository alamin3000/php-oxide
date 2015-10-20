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
      $_t_callables = [];
   
   
   /**
    * hasExtendedMethod function.
    * 
    * @access public
    * @param mixed $name
    * @return void
    */
   public function hasExtendedMethod($name) {
	   return (isset($this->_t_callables[$name]));
   }
         
   /**
    * Extend Object function (methods)
    * 
    * @access public
    * @param mixed $object
    * @return void
    */
   public function extendObject($object, $name = null, $method_prefix = '', $override = false) {
	   if(!is_object($object)) throw new \Exception("Not object.");
      $reflector = new \ReflectionClass($object);
      if(!$name) {
         $name = $reflector->getShortName();
      }
      
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
	      $name = $method_prefix . $method->getName();
	      $this->extendCallable($name, [$object, $name], $override);
      }
   }
   
      
   /**
    * Extend Class functions (static methods)
    * 
    * @access public
    * @param mixed $classname
    * @param mixed $instance
    * @param string $prefix (default: '')
    * @param bool $override (default: false)
    * @return void
    */
   public function extendClass($classname, $instance = null, $prefix = '', $override = false) {
	   $reflector = new \ReflectionClass($classname);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);
      foreach($methods as $method) {
	      $name = $prefix . $method->getName();
	      $this->extendCallable($name, [$classname, $name], $override);
      }
      if($instance === null) $instance = $classname;
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
      } else {
	      throw new \Exception("Method ({$name}) has not been extended.");
      }
   }
}