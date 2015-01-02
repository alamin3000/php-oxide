<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;

class ServiceContainer  {
   protected 
      $_throwException = false,
      $_services = [],
      $_closures = [];
   
   public function __construct($data = null) {
      parent::__construct($data);
   }
   
   /**
    * 
    * @param string $name
    */
   public function get($name) {
      if(isset($this->_services[$name])) {
         return $this->_services[$name];
      }
      
      if(isset($this->_closures[$name])) {
         $closure = $this->_closures[$name];
         $service = $closure($this);
         $this->_services[$name] = $service;
         return $service;
      }
      
      if($this->_throwException) {
         throw new \Exception('Service not found.');
      } else {
         return null;
      }
   }
   
   /**
    * 
    * @param string $name
    * @param mixed $service
    */
   public function set($name, $service) {
      if($service instanceof \Closure) {
         $this->_closures[$name] = $service;
      } else {
         $this->_services[$name] = $service;
      } 
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