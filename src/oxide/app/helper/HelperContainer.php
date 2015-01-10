<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\base\Container;
use oxide\http\Context;

class HelperContainer extends Container  {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_helpers = [
         'util' => 'oxide\app\helper\Util',         
         'html' => 'oxide\app\helper\Html',
         'flash' => 'oxide\app\helper\Flash', 
         'head' => 'oxide\app\helper\Head', 
         'ui' => 'oxide\app\helper\Ui'
      ],
           
      $_context = null;
   
   
   public function __construct(Context $context) {
      parent::__construct();
      $this->_context = $context;
      $this->registerDefaultHelpers($this->_helpers);
   }
   
   
   protected function registerDefaultHelpers(array $helpers) {
      foreach($helpers as $name => $helper) {
         $this->set($name, function($c) use($helper) {
            return new $helper($c);
         });
      }
   }


   /**
    * Get the application context object
    * 
    * @return Context
    */
   public function getContext() {
      return $this->_context;
   }
   
   public function __call($name, $arguments) {
      throw new \Exception('not implemented yet.');
      foreach($this->_container as $key => $instance) {
         if(method_exists($instance, $name)) {
            
         }
      }
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
}