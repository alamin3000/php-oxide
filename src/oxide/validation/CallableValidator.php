<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;

class CallableValidator implements Validator {
   use ValidatorMessageTrait;
   protected
      $_callable = null;
   
   public function __construct(callable $callable, $errorMessage = null) {
      $this->_callable = $callable;
      $this->setErrorMessage($errorMessage);
   }
   
   public function validate($value, Result &$result = null) {
      $callable = $this->_callable;
      $return = call_user_func_array($callable, [$value, $result]);
      return $this->prepareResult($return, $result);
   }
}