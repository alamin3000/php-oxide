<?php
namespace oxide\validation;
use oxide\base\Container;
/**
 * Validator Array
 * Contains multiple validators for performing validation against single value
 */
class ValidatorArray extends Container implements Validator {
   public 
      /**
       * @var bool Indicate if validation process should add
       */
      $breakOnFirstError = false;
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return null
    */
   public function validate($value, ValidationResult &$result = null) {
      $function = function(Validator $validator, $key, &$break) use (&$result, &$value) {
         $validator->validate($value, $result);
         
         if(!$result->isValid() && $this->breakOnFirstError) {
            $break = true;
         }      
      };
      
      $this->iterate($function);

		if(!$result->isValid()) {
         return NULL;
      }
      
      return $value;      
   }
}
