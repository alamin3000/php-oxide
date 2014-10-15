<?php
namespace oxide\validation\file;
use oxide\validation\ValidationResult;

/**
 * Image upload validator
 * 
 * Extends file upload validator and provides additional validation checks such as
 * Image sizes
 */
class ImageUploadValidator extends FileUploadValidator {
   protected
           $_max_width = null,
           $_max_height = null,
           $_min_width = null,
           $_min_height = null;
   
   /**
    * 
    * @param array $allowed_file_types
    */
   public function __construct(array $allowed_file_types = null, $max_filesize = null, $min_filesize = null) {
      parent::__construct($allowed_file_types, $max_filesize, $min_filesize);
   }
   
   /**
    * Sets max image size for validation.
    * 
    * @param int $width
    * @param int $height
    */
   public function setMaxImageSize($width = null, $height = null) {
      $this->_max_height = $height;
      $this->_max_width = $width;
   }
   
   /**
    * Sets min image size for validation.
    * 
    * @param int $width
    * @param int $height
    */
   public function setMinImageSize($width = null, $height = null) {
      $this->_min_height = $height;
      $this->_min_width = $width;
   }
   
   /**
    * generates validation error message.
    * @return string
    */
   protected function sizeRequirementString() {
      $str = '';
      if($this->_min_width) $str .= 'Width must be at least ' . $this->_min_width . 'px. ';
      if($this->_max_width) $str .= 'Width must be less than ' . $this->_max_width . 'px. ';
      if($this->_min_height) $str .= 'Width must be at least ' . $this->_min_height . 'px. ';
      if($this->_max_height) $str .= 'Width must be less than ' . $this->_max_height . 'px. ';
      
      return $str;
   }


   /**
    * Validate
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return \oxide\validation\ValidationResult|boolean
    */
   public function validate($value, ValidationResult &$result = null) {
      $return = parent::validate($value, $result);
      if($return) {
         $file = $value['tmp_name'];
         $info =  getimagesize($file);
         if(($this->_min_width && $info[0] < $this->_min_width) ||
            ($this->_max_width && $info[0] > $this->_max_width) ||
            ($this->_min_height && $info[1] < $this->_min_height) ||
            ($this->_max_height && $info[1] > $this->_max_height)) {
            $result->addError($this->sizeRequirementString());
            return false;
         }
      }
      
      return $result;
   }
}