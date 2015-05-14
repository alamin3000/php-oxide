<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */
namespace oxide\base;
use oxide\base\pattern\ResolverTrait;
use oxide\base\pattern\PropertyAccessTrait;
use oxide\base\pattern\ExtendableTrait;

/**
 * Container class.
 * Adds lazy loading support to Dictionary.
 * Object/resource can be assigned using closure for lazy loading when accessed for the first time
 * @note key/names will be stored in case insensitive
 */
class Container extends Dictionary {
   use ResolverTrait;
   
	public function offsetExists($key) {
		return parent::offsetExists($key) || $this->hasResolver($key);
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
}