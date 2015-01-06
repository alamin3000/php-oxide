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
      /**
       * @var Container Shared container
       */
      $_shared = null;
   
   protected
      $_context = null;
   
   public function __construct($data = null) {
      parent::__construct($data);
   }
   
   /**
    * Set helper
    * @param type $name
    * @param type $helper
    */
   public function setHelper($name, $helper) {
      $hname = ucfirst($name) . "Helper";
      self::sharedContainer()->set($hname, $helper);
   }
   
   /**
    * Get helper by name
    * @param string $name
    * @return mixed
    */
   public function getHelper($name) {
      $hname = ucfirst($name) . "Helper";
      return self::sharedContainer()->get($hname);
   }
   
   /**
    * Get the shared container among all views
    * 
    * @return Container
    */
   public static function sharedContainer() {
      if(!self::$_shared) {
         $instance = new Container();
         self::$_shared = $instance;
      }
      
      return self::$_shared;
   }
   
   /**
    * Share data accross all views
    * 
    * @param type $key
    * @param type $value
    */
   public function share($key, $value) {
      self::sharedContainer()->set($key, $value);
   }
   
   /**
    * Get the shared data
    * 
    * @param type $key
    * @return type
    */
   public function shared($key) {
      $shared = self::sharedContainer();
      if(isset($shared[$key])) {
         return $shared[$key];
      }
      
      return null;
   }
}