<?php
namespace oxide\validation\misc;
use oxide\validation\ValidationResult;
use oxide\validation\ValidatorAbstract;

class InArrayValidator extends ValidatorAbstract {
   protected
      $_array = null;
   public function __construct(array $array) {
      parent::__construct();
      $this->_array = $array;
   }
   
   
   public function validate($value, ValidationResult &$result = null) {
      $valid = false;
      if(in_array($value, $this->_array)) {
         $valid = true;
      }
      return $this->_returnResult($valid, $result, $value);
   }
}