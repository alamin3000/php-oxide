<?php
namespace oxide\base\pattern;

/**
 * Provides additional functionalities to the ArrayAccessTraits
 * 
 * Must have ArrayAccessTraits using.  The reason this trait doesn't use the 'use'
 * statement for auto loading ArrayAccessTrait is because unneccessary method overloading
 */
trait ArrayFunctionsTrait {
   
   
   /**
    * Add $content at the end of the array
    * 
    * @note if $offset is given and $offset already exists, then it will be removed first
    *       before replacing the new content
    * @param mixed $content
    */
   public function append($content, $offset = null) {      
      if($offset !== null) {
         // first we need to check if item already exists in the array with current offset
         // if so, we will need to remove and then add in order to make it 'append'
         if(isset($this->_t_array_storage[$offset])) {
            unset($this->_t_array_storage[$offset]);
         }
         
         $this->_t_array_storage[$offset] = $content;
      } else {
         $this->_t_array_storage[] = $content;
      }
   }
   
   /**
    * Add $content at the begining of the array
    * 
    * @note if $offset is given and $offset already exists, then it will be removed first
    *       before replacing the new content
    * @param mixed $content
    */
   public function prepend($content, $offset = null)  {      
      if($offset !== null) {
         // first we need to check if item already exists in the array with current offset
         // if so, we will need to remove and then add in order to make it 'prepend'
         if(isset($this->_t_array_storage[$offset])) {
            unset($this->_t_array_storage[$offset]);
         }
         
         $arr = &$this->_t_array_storage;
         $arr = $arr + $arr;
      } else {
         array_unshift($this->_t_array_storage, $content);
      }
   }
   
   /**
    * Insert object.  If $index is given, it will inserted at that location
    * @param type $value
    * @param int $index
    */
   public function insert($value, $offset = null, $index = null) {      
      if($index === null) {
         // this is same as normal insert
         $this->_t_array_storage[$offset] = $value; // $offset's null case will be dealt by offsetSet method
      } else {
         
         if($offset) {
            $value = [$offset => $value];
            $this->_t_array_storage = array_slice($this->_t_array_storage, 0, $index, true) 
                     + $value 
                     + array_slice($this->_t_array_storage, $index, NULL, true);

         } else {
            $value = [$value];
            array_splice($this->_t_array_storage, $index, 0, $value);
         }
         
      }
   }
   
  /**
    * 
    * @param type $callback
    * @param type $group
    * @param type $key
    * @return type
    */
   public function iterate(callable $callback, $key = null) {
      $break = false;
      
      if($key !== null && isset($this->_t_array_storage[$key])) {
         $value = $this->_t_array_storage[$key];
         $callback($value, $key, $break);
      } else {
         foreach ($this->_t_array_storage as $key => $value) {
            $callback($value, $key, $break);
            if($break) break;
         }
      }
   }
   
   public function merge(array $arr) {
      $this->_t_array_storage = array_merge($this->_t_array_storage, $arr);
   }
}