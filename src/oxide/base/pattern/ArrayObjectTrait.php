<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base\pattern;


/**
 * Adds additional functionalities to ArrayObject
 */
trait ArrayObjectTrait {
   /**
    * Add $content at the begining of the array
    * 
    * @param mixed $content
    */
   public function prepend($content, $offset = null)  {     
      $this->onArrayAccessSet($offset, $content);
      $array = $this->getArrayCopy();
      
      if($offset === null) {
         array_unshift($array, $content);
      } else {
      	$array = [$offset => $content] + $array;
      }
      
      $this->exchangeArray($array);
      return $this;
   }
   
   /**
    * Insert object.  
    * 
    * If $offset is given
    * If $index is given, it will inserted at that location
    * @param mixed $value
    * @param mixed $offset
    * @param int $index
    */
   public function insert($value, $offset = null, $index = null) {  
      $this->onArrayAccessSet($offset, $value);
      if($index === null) {
         // this is same as normal insert
         $this[$offset] = $value; // $offset's null case will be dealt by offsetSet method
      } else {
         $array = $this->getArrayCopy();
         
         if($offset) {
            $arrvalue = [$offset => $value];
            $array = array_slice($array, 0, $index, true) 
                     + $arrvalue 
                     + array_slice($array, $index, NULL, true);

         } else {
            $value = [$value];
            array_splice($array, $index, 0, $value);
         }
         
         $this->exchangeArray($array);
      }
      
      return $this;
   }
   
   /**
    * 
    * @param type $value
    * @return type
    */
   public function search($value) {
      return array_search($value, $this->getArrayCopy(), true);
   }
   
   /**
    * 
    * @param type $offset
    * @param type $value
    */
   public function offsetSet($offset , $value ) {
      $this->onArrayAccessSet($offset, $value);
      parent::offsetSet($offset, $value);
   }
   
   /**
    * 
    * @param type $offset
    */
   public function offsetUnset($offset) {
      if(isset($this[$offset])) {
         $value = $this[$offset];
         $this->onArrayAccessUnset($offset, $value);
      }
      
      parent::offsetUnset($offset);
   }
   
   protected function onArrayAccessSet(&$key, $value) { }
   protected function onArrayAccessUnset(&$key, $value) { }
}