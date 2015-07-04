<?php
namespace oxide\base;

class ArrayString extends \ArrayObject { 
   use \oxide\base\pattern\ArrayObjectTrait;
   
   protected 
      $_stringify_callback = null;
   

   /**
    * 
    * @param \Closure $callback
    */
   public function setStringifier(\Closure $callback) {
      $this->_stringify_callback = $callback;
   }
   
   
   /**
    * get current stringify callback.
    * 
    * @access public
    * @return void
    */
   public function getStringifier() {
	   return $this->_stringify_callback;
   }
   
   /**
    * Replaces current string with the given string.
    * 
    * @param string $str
    * @return void
    */
   public function replace($str) {
      $this->exchangeArray($str);
   }
   
   
   /**
    * __toString function.
    * 
    * @return string
    */
   public function __toString() {
      if($this->_stringify_callback) {
         $callback = $this->_stringify_callback;
         return $callback($this);
      } else {
         return implode('', $this->getArrayCopy());
      }
   }
}