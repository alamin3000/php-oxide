<?php
namespace oxide\validation\misc;
use oxide\validation\Filterer;

/**
 * 
 */
class RegexFilterer implements Filterer {
   protected 
      $_pattern = null,
      $_replace = null;

   /**
    * 
    * @param string $pattern
    * @param string $replace
    */
   public function __construct($pattern, $replace = '') {
      $this->_pattern = $pattern;
      $this->_replace = $replace;
   }
   
   /**
    * 
    * @param string $value
    * @return string
    */
   public function filter($value) {
      $pattern = (string)$this->_pattern;
      $replace = (string)$this->_replace;
      $value = preg_replace($pattern, $replace, (string) $value);
      
      return $value;
   }
}