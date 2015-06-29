<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base;

/**
 * Dictionary
 * 
 * Standard dictionary object
 * Provides assoicative array interface for storing key/value data
 */
class Dictionary extends \ArrayObject {
   /**
    * Construct the dictionary with given $data, if any
    * 
    * @param mixed $data
    */
   public function __construct($data = null) {
      parent::__construct();
      if($data) {
         $this->exchangeArray($data);
      }
   }
   
   /**
    * Get value using key path.  
    * 
    * @param string $keypath
    * @param mixed $default
    * @param char $pathseparator
    * @return mixed
    */
   public function getUsingKeyPath($keypath, $default = null, $required = null, $pathseparator = '.') {   
      if(!is_array($keypath)) {
      	$keys = explode($pathseparator, $keypath);
      } else {
	      $keys = $keypath;
         $keypath = implode('.', $keys);
      }
      
      $var = null;
      if(!empty($keys)) {
         $var = $this;
         foreach($keys as $key) {
            if(isset($var[$key])) {
               $var = $var[$key];
            } else {
               if($required) throw new \Exception("Required key path is {$keypath} not found.");
               else return $default;
            }
         }
      }
      
      return $var;
   }
   
   /**
    * get function.
    * 
    * @access public
    * @param mixed $key
    * @param mixed $default (default: null)
    * @param bool $required (default: false)
    * @return void
    */
   public function get($key, $default = null, $required = false) {
      if(is_array($key)) {
         $vals = [];
         foreach($key as $akey) {
            $vals[] = $this->get($akey, $default, $required);
         }
         
         return $vals;
      } else {
         if(!isset($this[$key])) {
            if($required) {
               throw new \Exception("Required key: {$key} not found.");
            } else {
               return $default;
            }
         }
         
         return $this[$key];
      }
   }

   /**
    * set function.
    * 
    * @access public
    * @param mixed $keys
    * @param mixed $value
    * @return self influet interface
    */
   public function set($keys, $value = null) {
      if(is_array($keys)) {
         foreach($keys as $key => $value) {
            $this[$key] = $value;
         }
      } else {
         $this[$keys] = $value;
      }
      
      return $this;
   }
}