<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\form;

abstract class ControlCollection extends Element {
   abstract public function getForm();
   
   
   public function renderInner() {
      $rowTag = new Tag('p');
      $errorTag = new Tag('strong');
      $buffer = '';
      $form = $this->getForm();
      $result = $form->getResult();
      $processor = $form->getValidationProcessor();
      
      foreach($this as $name => $element) {
         if($element instanceof Control) {
            $buffer.= $rowTag->renderOpen();
            if($processor->isRequired($name)) {
               $element->setLabel($element->getLabel() . '*');
            }
            
            $buffer.= $element->render();
            
            if($result->hasError($name)) {
               $buffer.= $errorTag->renderWithContent($result->getError($name));
            }
            
            $buffer.= $rowTag->renderClose();
         }
         
         else if($element instanceof Fieldset) {
            
         }
      }
   }
   
   /**
    * Override the element add method
    * 
    * We will only allow Control and Fieldset to be added
    * @param type $key
    * @param type $value
    */
   protected function onArrayAccessSet($key, $value) {
      parent::onArrayAccessSet($key, $value);
      if(!$value instanceof Control || !$value instanceof Fieldset) {
         throw new \Exception('Only controls and fieldset are allowed');
      } else {
         if(($form = $this->getForm())) {
            $value->setForm($form); // this will add control ref
         }
      }
   }
   
   /**
    * 
    * @param type $key
    * @param type $value
    */
   protected function onArrayAccessUnset($key, $value) {
      parent::onArrayAccessUnset($key, $value);
      if($value instanceof FormAware) {
         $value->setForm(null);
      }
   }
}