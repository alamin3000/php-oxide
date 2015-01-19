<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;
class Component implements ValidationComponent {
   protected 
      $_filterers = null,
      $_validators = null,
      $_processors = null;




   public function __construct(\Closure $function = null) {
      if($function) {
         $function($this);
      }
   }
   
   public function getFilterers() {
      if($this->_filterers === null) {
         $this->_filterers = new FiltererArray();
      }
      
      return $this->_filterers;
   }
   
   public function getValidators() {
      if($this->_validators === null) {
         $this->_validators = new ValidatorArray();
      }
      
      return $this->_filterers;
   }
   
   public function getProcessors() {
      if($this->_processors === null) {
         $this->_processors = new ProcessorArray();
      }
      
      return $this->_processors;
   }
   
   public function filter($value) {
      
   }
   
   
}