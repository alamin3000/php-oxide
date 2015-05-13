<?php
namespace oxide\base\pattern;

trait PropertyAccessTrait {
   protected 
      $_t_property_storage = [];
   
   /**
	 * Add data to the context
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->_t_prop_access_set($key, $value);
      $this->_t_property_storage[$key] = $value;
	}
	
	/**
	 * Get data from the context if available
	 * 
	 * @return mixed
	 * @param string $key
	 */
	public function __get($key) {
		$this->_t_prop_access_get($key);
      $value = $this->_t_property_storage[$key];
      if(is_array($value)) {
         return $value;
      } else {
         return $value;
      }
	}
   
   /**
    * 
    * @param string $key
    * @return bool
    */
   public function __isset($key) {
      return isset($this->_t_property_storage[$key]);
   }
   
   /**
    * 
    * @param type $key
    */
   public function __unset($key) {
	   if(isset($this->_t_property_storage[$key])) {
		   $value = $this->_t_property_storage[$key];
		   $this->_t_array_access_unset($key, $value);
			unset($this->_t_property_storage[$key]);
	   }
   }
   
   
   protected function _t_prop_access_set($key, $value) {}
   protected function _t_prop_access_get($key) {}
   protected function _t_prop_access_unset($key, $value) {}

}