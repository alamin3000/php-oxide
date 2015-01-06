<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\http\Context;

class HelperAbstract {
   use \oxide\base\pattern\SingletonTrait;
   
   protected
      $_context = null;
   
   protected function __construct(Context $context = null) {
      if($context) $this->setContext ($context);
   }
   
   public function setContext(Context $context) {
      $this->_context= $context;
   }
   
   public function getContext() {
      return $this->_context;
   }
   
   /**
    * 
    * @param type $name
    * @param \Closure $function
    */
   public function extendClosure($name, \Closure $function) {
      self::$_invokers[$name] = $function;
   }
   
   public function extendClass($class) {
      $reflector = new \ReflectionClass($class);
      $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach($methods as $method) {
         $this->_t_methods[$method] = $object;
      }
   }
   
   public function __call($name, $arguments) {
      if(self::$_invokers[$name]) {
         $callable = self::$_invokers[$name];
         
         return call_user_func_array($callable, $arguments);
      } else {
         throw new \Exception('Unable to find helper method: '. $name);
      }
   }
}