<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\mvc;
use oxide\base\Container;
use oxide\http\Context;
use oxide\mvc\View;
use oxide\Loader;

/**
 */
class ViewData extends Container {
   protected static
      $_shared = [];
   
   protected
      $_context = null,
      $_view = null;
   
   
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
   
   /**
    * Get 
    * @param type $key
    * @param type $default
    * @return type
    */
   public function get($key, $default = null) {
      if($this->offsetExists($key)) return $this->offsetGet($key);
      else if(self::$_shared[$key]) return self::$_shared[$key];
      else return $default;
   }   
   
   /**
    * Loads the given $helper and returns
    * 
    * @param type $helper
    * @return type
    */
   public static function helper($helper) {
      return Loader::loadHelper($helper);
   }
   
   /**
    * Set the view associated with this dictionary
    * 
    * @param View $view
    */
   public function setView(View $view) {
      $this->_view = $view;
   }
   
   /**
    * Get the view assoicated with the dictionary
    * 
    * @return View
    */
   public function getView() {
      return $this->_view;
   }
   
   /**
    * Get the http context
    * 
    * @return Context
    */
   public function getContext() {
      return $this->shared('context');
   }
}