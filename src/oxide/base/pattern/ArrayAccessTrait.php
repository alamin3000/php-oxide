<?php
namespace oxide\base\pattern;

/**
 * Common Array storage/access functionalities
 * 
 * This trait provides some useful functionalities to access and manupulate array
 * This trait implements various interfaces related to array:
 *    ArrayAccess, Countable
 * In order to take advantages of these, the class using this trait must implement 
 * these interfaces in declaration
 */
trait ArrayAccessTrait {
   protected 
      $_t_array_storage = [];
   
   /**
    * Get a copy of the internal array
    * 
    * @return array
    */
   public function toArray() {
      return $this->_t_array_storage;
   }
   
   /**
    * 
    * @param array $arr
    */
   public function setArray(array $arr) {
      foreach($arr as $key => $val) {
         $this->offsetSet($key, $val);
      }
      
      return $this;
   }
   
   /**
    * Get the internal array reference
    * 
    * @return type
    */
   public function &arrayRef() {
      return $this->_t_array_storage;
   }

   /**
    * Indicates if $offset exists
    * 
    * Implements ArrayAccess interface
    * @param mixed $offset
    * @return bool
    */
   public function offsetExists($offset )  {
      return isset($this->_t_array_storage[$offset]);
   }
      
   
   /**
    * Get object at $offset, if available
    * 
    * Implements ArrayAccess interface
    * @param mixed $offset
    * @return mixed
    */
   public function offsetGet($offset)  {
      return $this->_t_array_storage[$offset];
   }
   
   /**
    * Add an object $value at $offset
    * 
    * Implements ArrayAccess interface
    * @param mixed $offset
    * @param mixed $value
    */
   public function offsetSet($offset , $value )  {
      $this->_t_array_access_set($offset, $value);
      if (is_null($offset)) {
          $this->_t_array_storage[] = $value;
      } else {
          $this->_t_array_storage[$offset] = $value;
      }
   }
   
   /**
    * Remove the object at $offset
    * 
    * Implements ArrayAccess interface
    * @param mixed $offset
    */
   public function offsetUnset($offset) {
      if($this->_t_array_storage[$offset]) {
         $value = $this->_t_array_storage[$offset];
         $this->_t_array_access_unset($offset, $value);
         unset($this->_t_array_storage[$offset]);
      }
   }
   
   /**
    * Returns number of entries in the array
    * 
    * Implements Countable interface.
    * Class using this trait should implement Countable in the declaration
    * @return int
    */
   public function count($mode = 'COUNT_NORMAL') {
      return count($this->_t_array_storage, $mode);
   }
   
   
   protected function _t_array_access_set($key, $value) {}
   protected function _t_array_access_unset($key, $value) {}
}