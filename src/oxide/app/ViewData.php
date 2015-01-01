<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\base\Container;

/**
 */
class ViewData extends Container {
   protected static
      $_shared = [];
      
   /**
    * Share data accross all view
    * 
    * @param type $key
    * @param type $value
    */
   public function share($key, $value) {
      self::$_shared[$key] = $value;
   }
   
   /**
    * Get the shared data
    * 
    * @param type $key
    * @return type
    */
   public function shared($key) {
      if(isset(self::$_shared[$key])) {
         return self::$_shared[$key];
      }
      
      return null;
   }
}