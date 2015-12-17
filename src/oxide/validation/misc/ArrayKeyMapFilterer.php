<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;


class ArrayKeyMapFilterer implements Filterer{
   protected
      $_array = null;
   
   public function __construct(array $array) {
      $this->_array = $array;
   }
   
   public function filter($value) {
      if(isset($this->_array[$value])) {
         return $this->_array[$value];
      }
      
      return $value;
   }
}