<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base;
use oxide\base\pattern\ArrayAccessTrait;

/**
 * Dictionary
 * 
 * Standard dictionary object
 * Provides assoicative array interface for storing key/value data
 */
class Dictionary 
   implements \ArrayAccess, \Countable, \IteratorAggregate {
   use ArrayAccessTrait;
   
   protected 
      $_modifiedKeys = [];
   


   /**
    * Construct the dictionary with given $data, if any
    * 
    * @param mixed $data
    */
   public function __construct($data = null) {
      if($data) $this->setArray ($data);
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
	   if(!$this->offsetExists($key)) {
		   if($required) {
            throw new \Exception("Required key: {$key} not found.");
         } else {
            return $default;
         }
	   }
      
      return $this->offsetGet($key);
   }
   
   /**
    * set function.
    * 
    * @access public
    * @param mixed $key
    * @param mixed $value
    * @return void
    */
   public function set($key, $value) {
	   $this->offsetSet($key, $value);
   }
   
   /**
    * Overrides to add Dictionary $data param support
    * 
    * @param \oxide\base\Dictionary $data
    * @throws \InvalidArgumentException
    */
   public function setArray($data) {
      if(is_array($data)) {
         $this->_t_array_storage = $data;
      } else if($data instanceof Dictionary) {
         $this->_t_array_storage = $data->toArray();
      } else {
         throw new \InvalidArgumentException('Invalid data passed.');
      }
   }
   
   /**
    * Get the iterator
    * Implementing IteratorAggregate interface
    */
   public function getIterator() {
      foreach ($this->_t_array_storage as $item) {
          yield $item;
      }
   }
   
   
   protected function _t_array_access_set($key, $value) {
      
   }
}