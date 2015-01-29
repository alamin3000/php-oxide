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
      foreach($this->getIterator() as $filterer) {
         $value = $filterer->filter($value);
      }
      
      return $value;
   }
}  