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
    * extendObject function.
    * 
    * @access public
    * @param mixed $object
    * @return void
    */
   public function extendObject(\stdClass $object) {
      $reflector = new \ReflectionClass($object);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
	      $this->extendCallable($method, [$object, $method]);
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
   public function extendClosure($name, \Closure $closure) {
      $this->extendCallable($name, $closure);
   }
   
   /**
    * extendCallable function.
    * 
    * @access public
    * @param mixed $name
    * @param callable $callable
    * @return void
    */
   public function extendCallable($name, callable $callable) {
	   if(isset($this->_t_callables[$name])) throw new \Exception("Method {$name} already exists.");
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