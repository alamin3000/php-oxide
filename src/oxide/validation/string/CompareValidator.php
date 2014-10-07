<?php
namespace oxide\validation\string;
use oxide\validation\ValidatorAbstract;
use oxide\validation\ValidationResult;

/**
 * Compare validator
 *
 * compares two value
 */
class CompareValidator extends ValidatorAbstract {
   private 
      $_compareValue,
      $_case;

   /**
    *
    * @param string $value
    * @param string $error_message error message
    * @param bool $case indicates if case sensitive or not
    * @todo add case check feature
    */
   public function __construct($init_value, $case = false) {
      $this->_compareValue = $init_value;
      $this->_case = $case;
   }

   /**
    * validates
    * 
    * @param mixed $value
    * @param ValidatorResult $result
    */
   public function validate($value, ValidationResult &$result = null) {
      if( strcmp((string) $this->_compareValue, (string) $value) != 0) {
         return $this->_returnResult(false, $result, $value);
      }

      return $this->_returnResult(true, $result, $value);
   }
}