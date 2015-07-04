<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;

/**
 * ComponentTrait
 * 
 * Implement ValidationComponent interface behaviours
 */
trait ComponentTrait {
   protected
      $_t_filterers = null,
      $_t_validators = null,
      $_t_required = false;
   
   /**
    * 
    * @return ValidatorContainer
    */
   public function getValidators() {
      if($this->_t_validators === null) {
         $this->_t_validators = new ValidatorArray();
      }
      
      return $this->_t_validators;
   }
   
   /**
    * 
    * @return FiltererContainer
    */
   public function getFilterers() {
      if($this->_t_filterers === null) {
         $this->_t_filterers = new FiltererArray();
      }
      
      return $this->_t_filterers;
   }
   
   /**
    * 
    * @param type $bool
    * @return type
    */
   public function isRequired($bool = null) {
      if($bool === null) {
         return $this->_t_required;
      } else {
         $this->_t_required = $bool;
      }
   }
   
   /**
    * 
    * @param type $value
    * @return type
    */
   public function filter($value) {
      return $this->getFilterers()->filter($value);
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return type
    */
   public function validate($value, Result &$result = null) {
      return $this->getValidators()->validate($value, $result);
   }
}