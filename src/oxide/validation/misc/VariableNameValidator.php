<?php
namespace oxide\validation\misc;
use oxide\validation\ValidatorAbstract;
use oxide\validation\Result;

class VariableNameValidator extends ValidatorAbstract {
   protected 
      $_errorMessage = 'Invalid variable name';
   
   public function validate($value, Result &$result = null) {
      $ok = preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $value);
      return $this->_returnResult($ok, $result, $value);
   }
}