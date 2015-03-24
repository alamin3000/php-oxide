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
         $keypath = implode(' -> ', $keys);
      }
      
      $var = null;
      if(!empty($keys)) {
         $var = $this->toArray();
         foreach($keys as $key) {
            if(isset($var[$key])) {
               $var = $var[$key];
            } else {
               if($required) throw new \Exception("Required keypath {$keypath} not found.");
               else return $default;
            }
         }
      }
      
      return $var;
   }
   
   public function get($key, $default = null, $required = false) {
      if(!isset($this->_t_array_storage[$key]))  {
         if($required) {
            throw new \Exception("Required key: {$key} not found.");
         } else {
            return $default;
         }
      }
      
      return $this->_t_array_storage[$key];
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
    * 
    * @param type $offset
    * @return type
    */
   public function offsetGet($offset) {
      return $this->get($offset);
   }
   
   /**
    * Get the iterator
    * Implementing
    */
   public function getIterator() {
      foreach ($this->_t_array_storage as $item) {
          yield $item;
      }
   }
}