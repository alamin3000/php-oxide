<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base;

class Container 
   extends Dictionary {
   use pattern\ArrayFunctionsTrait;
   
   protected           
      /**
       * @var array Stores manually managed object instances from clousers
       */
      $_instances = [];
   
   /**
    * Get data from the container
    * 
    * Supports lazy loading using Closures.  If data is a Closure, it will be 
    * invoked and returned data will be sent instead.
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function get($key, $default = null, $required = false) {
      if(isset($this->_instances[$key])) return $this->_instances[$key]; 
      $value = parent::get($key, $default, $required);
      if($value instanceof \Closure) {
         $val = $value($this);
         $this->_instances[$key] = $val;
         return $val;
      } else {
         return $value;
      }
   }
      
   /**
    * Sets a data
    * 
    * @param string $key
    * @param mixed $data
    */
   public function set($key, $data) {
      $this->offsetSet($key, $data);
   }
   
   /**
    * Remove a data/service from the container
    * 
    * @param string $key
    */
   public function remove($key) {
      unset($this->_t_array_storage[$key]);
      if(isset($this->_instances[$key])) {
         $this->_instances[$key] = null;
         unset($this->_instances[$key]);
      }
   }
   
   public function offsetUnset($offset) {
      $this->remove($offset);
   }
   
   public function __call($name, $arguments) {
      $action = substr($name, 0, 3);
		$service = substr($name, 3);
		
		if(!$service) {
         throw new \Exception("Invalid method called");
		}
      
      if($action == 'get') {
         return $this->get($service);
      } else if($action == 'set') {
         $param = current($arguments);
         return $this->set($service, $param);
      } else {
			throw new \Exception("Invalid method called: \"$name\"");
		}
   }
}