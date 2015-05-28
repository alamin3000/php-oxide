<?php
namespace oxide\ui;
use oxide\base\Stringify;
use oxide\base\Dictionary;

class ArrayString 
   extends Dictionary 
   implements Stringify, Renderer { 
   use \oxide\base\pattern\ArrayFunctionsTrait;
   
   protected 
      $_stringify_callback = null;

   /**
    * 
    * @param \Closure $callback
    */
   public function setStringifyCallback(\Closure $callback) {
      $this->_stringify_callback = $callback;
   }
   
   
   /**
    * get current stringify callback.
    * 
    * @access public
    * @return void
    */
   public function getStringifyCallback() {
	   return $this->_stringify_callback;
   }
   
   /**
    * Replaces current string with the given string.
    * 
    * @param string $str
    * @return void
    */
   public function replace($str) {
      $this->_t_array_storage = [$str];
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
         return self::toString($this->_t_array_storage);
      }
   }
   
   /**
    * Get string from given $args
    * 
    * @param mixed $arg
    * @return string
    */
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
   
   /**
    * 
    * @return string
    */
   public function render() {
	   return $this->__toString();
   }
}