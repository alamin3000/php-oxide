<?php
namespace oxide\util;
use oxide\base\Stringify;
use oxide\base\Container;

class ArrayString 
   extends Container 
   implements Stringify {   
   protected 
      $_stringify_callback = null;

   public function registerStringifyCallback(callable $callback) {
      $this->_stringify_callback = $callback;
   }
   
   public function __toString() {
      if($this->_stringify_callback) {
         $callback = $this->_stringify_callback;
         return $callback($this);
      } else {
         return implode('', $this->_t_array_storage);
      }
   }
}