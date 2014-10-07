<?php
namespace oxide\validation;


class CallableFilterer implements Filterer
{
   protected 
           $_callable = null;


   /**
    * 
    * @param callable $callable
    */
   public function __construct(callable $callable)
   {
      $this->_callable = $callable;
   }
   
   
   /**
    * 
    * @param type $value
    */
   public function filter($value)
   {
      return $this->_callable($value, $this);
   }
}