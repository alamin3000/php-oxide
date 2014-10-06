<?php
namespace oxide\http;
use oxide\std\Object;
/**
 * Context class
 * 
 * Context is a simple concept of storing data and services for the current http session.
 * to access any data, simply use direct accessors ex. $context->someData = "something";
 * to access any service, use get/set method. ex $context->setService_Name($service_instance);
 * 
 * @package oxide
 * @subpackage http
 */
class Context extends Object
{
   use
      \oxide\util\pattern\DefaultInstanceTrait;
   
   protected
      $_instances = [],
      $_services = [];

	public function __construct($data = null) {
		parent::__construct($data);
		$this->set('request', function() { return Request::defaultInstance(); });
		$this->set('response', function() { return Response::defaultInstance(); });
	}

   /**
    * 
    * @return Request
    */
	public function getRequest() {
		return $this->get('request');
	}

   /**
    * 
    * @return Response
    */
	public function getResponse() {
		return $this->get('response');
	}

   public function __get($key) {
      if(isset($this->_instances[$key])) return $this->_instances[$key]; 
      $value = parent::__get($key);
      if(isset($this->_services[$key])) {
         return $this->_instances[$key] = $value($this);
      } else return $value;
   }
   
   public function __set($key, $value) {
      if($value && $value instanceof \Closure) { // if this is a closure, treat as service
         $this->_services[$key] = true;
      }
      parent::__set($key, $value);
   }
   
   public function __unset($key) {
      parent::__unset($key);
      if($this->_services[$key]) {
         $this->_instances[$key] = $this->_services[$key] = NULL;
         unset($this->_services[$key]);
         unset($this->_instances[$key]);
      }
   }

	public function required($key) {
		if($this->__isset($key)) {
			return $this->__get($key);
		} else {
			throw new \Exception("Key: $key is required in the context.");
		}
	}
}