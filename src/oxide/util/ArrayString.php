<?php
namespace oxide\util;


class ArrayString extends ArrayContainer implements Stringify
{   
   protected 
      $_stringify_callback = null;

   public function registerStringifyCallback(callable $callback) {
      $this->_stringify_callback = $callback;
   }
   
   public function clear($content = null) {
      $this->_t_array_storage = [$content];
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