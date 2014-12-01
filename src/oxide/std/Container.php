<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\std;

class Container extends Object 
   implements 
      \ArrayAccess, \Countable, \IteratorAggregate {
   
   private
      /**
       * @var array All container data
       */
      $_data = [],
           
      /**
       * @var array Stores manually managed object instances from clousers
       */
      $_instances = [];
   
   /**
    * 
    * @param type $key
    * @param type $default
    * @return type
    */
   public function get($key, $default = null) {
      if(isset($this->_instances[$key])) return $this->_instances[$key]; 
      if(!isset($this->_data[$key]))  {
         return $default;
      }
      
      $value = $this->_data[$key];
      if($value instanceof \Closure) {
         $val = $value($this);
         $this->_instances[$key] = $val;
         return $val;
      } else {
         return $value;
      }
   }
   
   /**
    * 
    * @param type $key
    * @param \Closure $value
    */
   public function set($key, $value) {
      $this->_data[$key] = $value;
   }
   
   /**
    * 
    * @param type $key
    */
   public function remove($key) {
      unset($this->_data[$key]);
      if(isset($this->_instances[$key])) {
         $this->_instances[$key] = null;
         unset($this->_instances[$key]);
      }
   }
   
   /**
    * 
    */
   public function getIterator() {
      $keys = array_keys($this->_data);
      foreach ($keys as $key) {
         yield $this->get($key);
      }
   }
   
   /**
    * 
    * @param type $key
    * @return type
    */
   public function exists($key) {
      return (isset($this->_data[$key]));
   }
   
   public function count($mode = 'COUNT_NORMAL') {
      return count($this->_data, $mode);
   }
   
   public function offsetExists($offset) {
      return $this->exists($offset);
   }
   
   public function offsetUnset($offset) {
      $this->remove($offset);
   }
   
   public function offsetGet($offset) {
      return $this->get($offset);
   }
   
   public function offsetSet($offset, $value) {
      $this->set($offset, $value);
   }
   
   public function __set($name, $value) {
      $this->set($name, $value);
   }
   
   public function __get($name) {
      return $this->get($name);
   }
   
   public function __isset($name) {
      return $this->exists($name);
   }
   
   public function __unset($name) {
      $this->remove($name);
   }
}