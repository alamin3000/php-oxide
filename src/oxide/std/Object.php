<?php
namespace oxide\std;

/**
 * A Generic object with data 
 * Supports readonly properties and modification tracking
 */
class Object implements \IteratorAggregate {
   use \oxide\util\pattern\PropertyAccessTrait;
   protected
      $_services = [],
      $_readonly = [],
      $_modified = [];

   
   /**
    * @see setData
    * @param type $data
    */
   public function __construct($data = null) {
      if($data) $this->setData ($data);
   }

   /**
    * Set/initialize data.  If Closure passed, it will be called with scope set to this object.
    * @param array|\Closure $data
    */
   public function setData($data) {
      if($data instanceof \Closure) {
         $data = $data->bindTo($this, $this);
         $data($this);
      } 
      $this->_t_property_storage = (array) $data;
   }
   
   protected function _t_property_modify($offset, $value) {
      if(isset($this->_readonly[$offset])) {
         throw new \Exception("{$offset} is read-only; therefore, cannot be set.");
      }
      
      $this->_modified[$offset] = $value;
      return true;
   }

   /**
    * Get element/property from the object
    * @param string $key
    * @param mixed $default if $key is not found, this value will be passed back
    * @return mixed
    */
   public function get($key, $default = null) {
      if($this->__isset($key))
         return $this->__get($key);
      else return $default;
   }

   /**
    * Sets the $value for $key.  Optionally mark the key as $readonly.
    * 
    * If set to readonly, this cannot be undone for the lifetime of this object.
    * @param string $key
    * @param type $value
    * @param bool $readonly
    * @return type
    * @throws \Exception
    */
   public function set($key, $value, $readonly = false) {
      $this->__set($key, $value);
      if($readonly) $this->_readonly[$key] = true;
   }

   /**
    * Checks to see if given $key exist in the data set
    * @param string $key
    * @return bool
    */
   public function exists($key) {
      return $this->__isset($key);
   }

   /**
    * Remove the given $key from the data
    * @param string $key
    */
   public function remove($key) {
      return $this->__unset($key);
   }

   /**
    * Get an array of all modified keys.  Returns an empty array of nothing has changed since last instanciated.
    * @access public
    * @return array 
   */
   public function getModifiedKeys() {
     return array_keys($this->_modified);
   }

   /**
    * Get Iterators (implements IteratorAggregator).  Uses generator
    */
   public function getIterator() {
      foreach($this->_t_property_storage as $value) {
         yield $value;
      }
   }      
}