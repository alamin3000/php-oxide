<?php
namespace oxide\validation;
use oxide\base\Container;

class FiltererContainer extends Container implements Filterer {
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