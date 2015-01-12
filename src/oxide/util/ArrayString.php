<?php
namespace oxide\util;
use oxide\base\Stringify;
use oxide\base\Dictionary;

class ArrayString 
   extends Dictionary 
   implements Stringify { 
   use \oxide\base\pattern\ArrayFunctionsTrait;
   
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