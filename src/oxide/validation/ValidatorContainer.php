<?php
namespace oxide\validation;
use oxide\base;
/**
 * Validator Array
 * Contains multiple validators for performing validation against single value
 */
class ValidatorContainer extends base\Container implements Validator {
   public 
      /**
       * @var bool Indicate if validation process should add
       */
      $breakOnFirstError = false;
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return null
    */
   public function validate($value, Result &$result = null) {
      foreach($this->getIterator() as $validator) {
         $validator->validate($value, $result);
         if(!$result->isValid() && $this->breakOnFirstError) {
            break;
         }
      }
      
      if(!$result->isValid()) return NULL;
      return $value;
   }
}
