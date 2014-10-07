<?php
namespace oxide\validation\string;

class ReplaceFilterer implements \oxide\validation\Filterer {
   protected 
      $_search_string = null,
      $_replace_string = null,
      $_count = null;


   public function __construct($search, $replace, $count= null) {
      $this->_search_string = $search;
      $this->_replace_string = $replace;
      $this->_count = $count;
   }
   
   public function filter($values) {
      $value = str_replace($this->_search_string, $this->_replace_string, $values, $this->_count);
      return $value;
   }
}