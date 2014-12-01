<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\mvc;
use oxide\std\Dictionary;
use oxide\mvc\View;

/**
 */
class ViewData extends Dictionary {
   protected static
      $_shared = [];
   
   protected
      $_view = null;
   
   
   public function share($key, $value) {
      self::$_shared[$key] = $value;
   }
   
   public function get($key, $default = null) {
      if($this->offsetExists($key)) return $this->offsetGet($key);
      else if(self::$_shared[$key]) return self::$_shared[$key];
      else return $default;
   }
   
   public static function helper($helper) {
      return Loader::loadHelper($helper);
   }
   
   public function setView(View $view) {
      $this->_view = $view;
   }
   
   public function getView() {
      return $this->_view;
   }
   
   public function getContext() {
      
   }
}