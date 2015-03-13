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
    * @param mixed $content
    */
   public function append($content) {
      $this->_t_array_access_set(null, $content);
      $this->_t_array_storage[] = $content;
   }
   
   /**
    * Add $content at the begining of the array
    * 
    * @param mixed $content
    */
   public function prepend($content)  {     
      $this->_t_array_access_set(null, $content);
      array_unshift($this->_t_array_storage, $content);
   }
   
   /**
    * 
    * @param type $value
    * @return type
    */
   public function search($value) {
      return array_search($value, $this->_t_array_storage, true);
   }
      
   /**
    * Insert object.  
    * 
    * If $index is given, it will inserted at that location
    * @param mixed $value
    * @param mixed $offset
    * @param int $index
    */
   public function insert($value, $offset = null, $index = null) {  
      $this->_t_array_access_set($offset, $value);
      if($index === null) {
         // this is same as normal insert
         $this->_t_array_storage[$offset] = $value; // $offset's null case will be dealt by offsetSet method
      } else {
         if($offset) {
            $arrvalue = [$offset => $value];
            $this->_t_array_storage = array_slice($this->_t_array_storage, 0, $index, true) 
                     + $arrvalue 
                     + array_slice($this->_t_array_storage, $index, NULL, true);

         } else {
            $value = [$value];
            array_splice($this->_t_array_storage, $index, 0, $value);
         }
      }
   }
   
   /**
    * Uses array_replace_recursive
    * 
    * @param array $arr
    */
   public function merge(array $arr) {
      $this->_t_array_storage = array_replace_recursive($this->_t_array_storage, $arr);
   }
}