<?php
namespace oxide\validation\file;

class FileExistsValidation extends \oxide\validation\ValidatorAbstract
{
   public
      /**
       * @var int 
       */
      $validationType = null;
   

   const
      VALIDATE_ON_EXIST = 1,
      VALIDATE_ON_INEXIST = 2;
   
   public function __construct($validationType = self::VALIDATE_ON_EXIST)
   {
      parent::__construct();
      $this->validationType = $validationType;

      if($validationType == self::VALIDATE_ON_EXIST) {
         $this->_errorMessage = 'File does not exist';
      } else {
         $this->_errorMessage = 'File already exists';
      }
   }
   
   public function validate($value, \oxide\validation\ValidationResult &$result = null)
   {
      $bool = file_exists($value);
      if($this->validationType == self::VALIDATE_ON_INEXIST) {
         $bool = !$bool;
      }
      
      return $this->_returnResult($bool, $result, $value);
   }
}
