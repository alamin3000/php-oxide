<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;

class HelperManager {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_helpers = [],
      $_registry = [];
   
   /**
    * Add an instance of helper 
    * @param type $name
    * @param type $helper
    */
   public function addHelper($name, $helper) {
      $this->_helpers[$name] = $helper;
   }
   
   public function registerHelperNamespace($namespace) {
      
   }
   
   public function loadHelper($name) {
      if(isset($this->_helpers[$name])) {
         return $this->_helpers[$name];
      }
      
      foreach($this->_registry as $namespace) {
         $class = ucfirst($name);
         $classname = "{$namespace}\\{$class}";
         
         if(class_exists($classname)) {
            $helper = new $classname();
         }
      }
   }
   
   
   public function getHelper($name) {
      
   }
}