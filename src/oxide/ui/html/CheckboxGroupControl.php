<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;

class CheckboxGroupControl extends Control {
   protected
      $_checkboxTemplateTag = null;
   
   public function __construct($name, $value = null, $label = null) {
      parent::__construct($name, $value, $label);
   }
   
   public function setData($data) {
      parent::setData($data);
      if(!is_array($data)) {
         throw new \Exception('Data must be an associative array.');
      }
   }
   
   /**
    * 
    * @return CheckboxControl
    */
   public function getTemplateCheckboxTag() {
      if($this->_checkboxTemplateTag === null) {
         $this->_checkboxTemplateTag = new CheckboxControl($this->getName() . '[]');
      }
      
      return $this->_checkboxTemplateTag;
   }
   
   public function renderInner() {
      if(empty($this->_data)) return '';
      
      $buffer = '';
      $tag = $this->getTemplateCheckboxTag();
      $tag->labelPosition = self::RIGHT;
      foreach($this->_data as $label => $value) {
         $tag->setLabel($label);
         $tag->setValue($value);
         $buffer .= $tag->render();
      }
      
      return $buffer;

   }
}