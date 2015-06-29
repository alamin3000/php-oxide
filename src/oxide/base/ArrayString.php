<?php
namespace oxide\base;

class ArrayString extends \ArrayObject { 
   use \oxide\base\pattern\ArrayObjectExtTrait;
   
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
         return self::toString($this);
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