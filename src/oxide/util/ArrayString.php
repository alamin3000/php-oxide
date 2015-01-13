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
         return self::toString($this->_t_array_storage);
      }
   }
   
   public static function toString($arg) {
      if(is_scalar($arg)) return $arg;
      else if($arg instanceof \oxide\ui\Renderer) return $arg->render();
      else if($arg instanceof Stringify) return $arg->__toString();
      else if(is_array ($arg) || $arg instanceof \Iterator) {
         $buf = '';
         foreach($arg as $val) {
            $buf .= self::toString($val);
         }
         return $buf;
      }
      else return (string) $arg;
   }
}