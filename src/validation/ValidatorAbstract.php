<?php
namespace oxide\validation;

abstract class ValidatorAbstract implements Validator
{
	protected
      $_errorMessage = '';
   

   /**
    * construct new validator with given name
    *
    * name is used to construct error messages
    * @param string $name
    */
   public function  __construct() 
   {
   }

   /**
    * set message template for the validator
    *
    * This message will be passed back when validtion fails
    * Different validators may have different templates.
    * @param string $str
    */
   public function setErrorMessage($str)
   {
      if($str) {
         $this->_errorMessage = $str;
      }
   }

   /**
    * get the error message
    *
    * If validation error occures for this validator
    * then this method should be used to get error message intended for display
    * @return string
    */
   public function getErrorMessage()
   {
      return $this->_errorMessage;
   }

   /**
    * this is protected utility function which will simply create a ValidatorResult object and return it
	 * 
    * @param bool $bool
    */
   protected function _returnResult($valid, ValidationResult &$result = null, $value = null)
   {
      if(!$result) {
         $result = new ValidationResult();
      }

      if(!$valid) {
         $result->addError($this->getErrorMessage());
      }

      // if valid we will return the original value
      return $valid;
   }
}