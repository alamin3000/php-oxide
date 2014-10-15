<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;

/**
 * 
 */
class Fieldset extends Control {
   use ControlAccessTrait;

   public function  __construct($name, $values = null, $legend = null, $attributes = null) {
		parent::__construct('fieldset', $name, $values, $legend, $attributes);
      $this->getLabelTag()->setTag('legend');
	}

   /**
    * 
    * @param Form $form
    */
   public function setForm(Form $form = null) {
      parent::setForm($form); // set own
      // we will apply form value to all entries
      foreach($this->_t_array_storage as $control) {
         if($control instanceof Control) { // this will also include fieldset, since it is also control
            $control->setForm($form);
         }
      }
   }
 
   protected function onPreRender(ArrayString $buffer) {
      $this->prepend($this->renderLabel());
   }
   
   protected function onPostRender(ArrayString $buffer) {
      // don't do anything
   }
}