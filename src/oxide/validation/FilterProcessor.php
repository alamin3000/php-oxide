<?php
namespace oxide\validation;

/**
 * Special processor that wraps a filter and makes into a processor
 * 
 */
class FilterProcessor implements Processor {
   protected 
           $_filterer = null;
   
   /**
    * 
    * @param \oxide\validation\Filterer $filterer
    */
   public function __construct(Filterer $filterer)  {
      $this->_filterer = $filterer;
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidatorResult $result
    * @return type
    */
   public function process($value, Result &$result = null)  {
      $filtered =  $this->_filterer->filter($value);
      return $filtered;
   }
}