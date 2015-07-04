<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;

trait ValidatorMessageTrait {
   protected
      $_t_validator_message = null;
   
   
   /**
    * set message template for the validator
    *
    * This message will be passed back when validtion fails
    * Different validators may have different templates.
    * @param string $str
    */
   public function setErrorMessage($str) {
      $this->_t_validator_message = $str;
   }

   /**
    * get the error message
    *
    * If validation error occures for this validator
    * then this method should be used to get error message intended for display
    * @return string
    */
   public function getErrorMessage() {
      return $this->_t_validator_message;
   }
   
   /**
    * Prepares the result with message
    * 
    * @param bool $bool
    * @param \oxide\validation\Result $result
    * @return bool
    */
   protected function prepareResult($bool, &$result = null) {
      if(!$result) {
         $result = new Result();
      }

      if($bool === false) {
         $result->addError($this->getErrorMessage());
      }

      // if valid we will return the original value
      return $bool;
   }
}