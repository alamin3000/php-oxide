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
 * Container class.
 * Adds lazy loading support to Dictionary.
 * Object/resource can be assigned using closure for lazy loading when accessed for the first time
 * @note key/names will be stored in case insensitive
 */
class Container extends Dictionary {
   protected       
   	$_binds = [];
   
	/**
	 * Bind a closure to the container for given $name.
	 * 
	 * @param string $name
	 * @param \Closure $closure
	 * @return void
	 */
	public function bind($name, \Closure $closure) {
		$this->_binds[$name] = $closure;
	}
   
	/**
	 * resolve function.
	 * 
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function resolve($name) {
		if(!isset($this->_binds[$name])) {
			return null;
		}
		
		$closure = $this->_binds[$name];
		$object = $closure($this);
		return $object;
	}
	
	
	public function offsetExists($key) {
		$bool = parent::offsetExists($key);
		if(!$bool) {
			return (isset($this->_binds[$key]));
		} else {
			return $bool;
		}
	}
	
	public function offsetGet($key) {
		if(parent::offsetExists($key)) {
			return parent::offsetGet($key);	
		}
		
		$val = $this->resolve($key);
		if($val) {
			$this->_t_array_storage[$key] = $val;
		}
		
		return $val;
	}

      
//   public function __call($name, $arguments) {
//      $action = substr($name, 0, 3);
//		$service = substr($name, 3);
//		if(!$service) {
//         throw new \Exception("Invalid method called");
//		}
//		
//		$service = strtolower($service);
//      if($action == 'get') {
//         return $this->get($service);
//      } else if($action == 'set') {
//         $param = current($arguments);
//         return $this->set($service, $param);
//      } else {
//			throw new \Exception("Invalid method called: \"$name\"");
//		}
//   }
}