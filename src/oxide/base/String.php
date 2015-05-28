<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base;

class String implements Stringify {
   protected 
      $_string = '';
   
   /**
    * 
    * @param string $str
    * @return self
    */
   public function append($str) {
      $this->_string .= $str;
      
      return $this;
   }
   
   /**
    * 
    * @param string $str
    * @return self
    */
   public function prepend($str) {
      $this->_string = $str . $this->_string;
      
      return $this;
   }
   
   public function __toString() {
      return $this->_string;
   }
}
