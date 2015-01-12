<?php
namespace oxide\validation;
use oxide\base\Container;

class FiltererArray extends Container implements Filterer {
   use \oxide\base\pattern\ArrayFunctionsTrait;
   
   /**
    * 
    * @param type $value
    */
   public function filter($value) {
      $callback = function(Filterer $filter, $key, &$break) use (&$value) {
         $value = $filter->filter($value);
      };
      
      $this->iterate($callback);
      
      return $value;
   }
   
}