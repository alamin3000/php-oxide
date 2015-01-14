<?php
namespace oxide\validation;


/**
 * Special validator class that wraps a filter into validator
 * 
 * The way it works is by comparing filtered value with the original value.
 * If these two values does NOT match then validation has failed.
 */
class FilterValidator extends ValidatorAbstract
{
   protected 
           $_filterer = null,
           $_errorMessage = null;
   
   /**
    * 
    * @param \oxide\validation\Filterer $filterer filterer to be used for validation purpose
    * @param type $error_message error message when validation fails
    */
   public function __construct(Filterer $filterer, $error_message = null) 
   {
      $this->_filterer = $filterer;
      
      if($error_message) {
         $this->_errorMessage = $error_message;
      } else {
         $this->_errorMessage = 'Validation failed for :' . get_class($filterer);
      }
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return bool
    */
   public function validate($value, Result &$result = null) 
   {
      $bool = true;
      $filtered = $this->_filterer->filter($value);
      if($filtered !== $value) {
         // the values doesn't match
         // this mean some kind of filters was done
         // so assuming validation failed
         $bool = false;
      }
      
      
      return $this->_returnResult($bool, $result, $value);
   }
}