<?php
namespace oxide\http;
use oxide\std\Object;
use oxide\util\pattern\DefaultInstanceTrait;
/**
 * Context class
 * 
 * Context a container of storing data and services for the current http session.
 * Provides lazy loading functionality
 * @package oxide
 * @subpackage http
 */
class Context extends Object {
   use
      DefaultInstanceTrait;
   
   protected
      $_instances = [],
      $_services = [];

   /**
    * 
    * @param type $data
    */
	public function __construct($data = null) {
		parent::__construct($data);
		$this->set('request', function() { return Request::currentServerRequest(); });
		$this->set('response', function() { return Response::defaultInstance(); });
	}

   /**
    * Current application request object
    * @return Request
    */
	public function getRequest() {
		return $this->get('request');
	}

   /**
    * Current application response object
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

   /**
    * Checks id given $key exists, if not an exception will be through
    * @param type $key
    * @return type
    * @throws \Exception
    */
	public function required($key) {
		if($this->__isset($key)) {
			return $this->__get($key);
		} else {
			throw new \Exception("Key: $key is required in the context.");
		}
	}
}