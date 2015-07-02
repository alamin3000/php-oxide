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
		$this->onPropertyAccessSet($key, $value);
      $this->_t_property_storage[$key] = $value;
	}
	
	/**
	 * Get data from the context if available
	 * 
	 * @return mixed
	 * @param string $key
	 */
	public function __get($key) {
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
		   $this->onPropertyAccessUnset($key, $value);
			unset($this->_t_property_storage[$key]);
	   }
   }
   
   
   protected function onPropertyAccessSet($key, $value) {}
   protected function onPropertyAccessUnset($key, $value) {}

}