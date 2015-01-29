<?php
namespace oxide\validation;


class CallableFilterer implements Filterer {
   protected 
      $_callable = null;


   /**
    * 
    * @param callable $callable
    */
   public function __construct(callable $callable) {
      $this->_callable = $callable;
   }
   
   
   /**
    * 
    * @param type $value
    */
   public function filter($value) {
      $callable = $this->_callable;
      if($callable) {
         return call_user_func($callback, $value);
      } else {
         return $value;
      }
   }
}