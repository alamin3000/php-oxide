<?php
namespace oxide\validation;

interface  Validator
{
   /**
    * Validates given $value and updates the validation result in given $result param
    * 
    * @note: validator is supposed to return value only if validates
    *       this may have problem in some situation, i.e when we validate NULL value
    *       therefore it is preferred to rely on ValidationResult object for validation check, not the returned value
    * @param mixed $value
    * @param ValidatorResult $result
    * @return null|mixed should return the $value if validates, else return null
    */
   public function validate($value, ValidationResult &$result = null);
}