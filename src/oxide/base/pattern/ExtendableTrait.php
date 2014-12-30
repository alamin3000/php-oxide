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
      $_t_instance_methods = [],
      $_t_static_methods = [];
   
   public function extendObject($object) {
      $reflector = new \ReflectionClass($object);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
         $this->_t_methods[$method] = $object;
      }
   }
   
   public function extendClosure($name, \Closure $closure) {
      $this->_t_methods[$name] = $closure;
   }
   
   public function callExtendedMethod($name, array $args = null) {
      if(isset($this->_t_methods[$name])) {
         $callable = $this->_t_methods[$name];
         
         if(is_object($callable)) {
            
         } else if($callable instanceof \Closure) {
            
         } else {
            
         }
//         call_user_func_array($callback, $args)
      }
   }
}