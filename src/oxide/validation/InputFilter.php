<?php
namespace oxide\validation;

class InputFilter implements Filterer, Validator {
   use ValidatorMessageTrait;
   
   protected 
      $_type = null,
      $_options = null,
      $_flags = null;
   
   public function __construct($type, $options = null, $flag = null) {
      $this->_type = $type;
      $this->_options = $options;
      $this->_flags = $flag;
   }
   
   /**
    * 
    * @return int
    */
   public function getType() {
      return $this->_type;
   }
   
   /**
    * 
    * @param type $type
    */
   public function setType($type) {
      $this->_type = $type;
   }
   
   public function setOptions($options) {
      
   }
   
   /**
    * 
    * @param mixed $flag
    */
   public function setFlag($flags) {
      $this->_flags = $flags;
   }
   
   /**
    * 
    * @return mixed
    */
   public function getFlag() {
      return $this->_flags;
   }
   
   /**
    * 
    * @param mixed $value
    */
   public function filter($value) {
      return filter_var($value, $this->_type, $this->_options);
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return type
    */
   public function validate($value, Result &$result = null) {
      $bool = filter_var($value, $this->_type, $this->_options);
      
      return $this->prepareResult($bool, $result);
   }
}