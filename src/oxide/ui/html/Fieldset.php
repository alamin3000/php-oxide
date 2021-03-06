<?php
namespace oxide\ui\html;
use oxide\base\ArrayString as String;

/**
 * 
 */
class Fieldset extends Element implements FormAware  {
   public
      /**
       * @var Tag Legend rendering tag
       */
      $legendTag = null;
   
   protected 
      $_name = null,
      $_form = null,
      $_legend = null;


   public function  __construct($name = null, $legend = null, $attributes = null) {
		parent::__construct('fieldset', null, $attributes);
      $this->setName($name);
      $this->setLegend($legend);
	}
   
   /**
    * 
    * @param string $name
    */
   public function setName($name) {
      $this->_name = $name;
   }
   
   /**
    * 
    * @return string
    */
   public function getName() {
      return $this->_name;
   }
   
   /**
    * Set the legend 
    * 
    * @param string $legend
    */
   public function setLegend($legend) {
      $this->_legend = $legend;
   }
   
   /**
    * Get the current legend
    * 
    * @return string
    */
   public function getLegend() {
      return $this->_legend;
   }
   
   /**
    * 
    * @param Form $form
    */
   public function setForm(Form $form = null) {
      $this->_form = $form;
      // we will apply form value to all entries
      foreach($this as $element) {
         if($element instanceof FormAware) { // this will also include fieldset, since it is also control
            $element->setForm($form);
         }
      }
   }
   
   /**
    * Get the form associated with the fieldset, if any.
    * 
    * @return Form
    */
   public function getForm() {
      return $this->_form;
   }

   /**
    * Add the legend after the opening tag
    * 
    * @param ArrayString $buffer
    */
   protected function onRenderOpen(String $buffer) {
      parent::onRenderOpen($buffer);
      if(($legend = $this->getLegend())) {
         $tag = ($this->legendTag) ? $this->legendTag : new Tag('legend');
         $buffer[] = $tag->renderWithContent($legend);
      }
   }
   
   
   /**
    * Override the element add method
    * 
    * We will only allow Control and Fieldset to be added
    * @param type $key
    * @param type $value
    */
   protected function onArrayAccessSet(&$key, $value) {
      parent::onArrayAccessSet($key, $value);
      if($value instanceof FormAware) {
         if($this->_form) $value->setForm($this->_form); // this will add control ref
      } else {
         throw new \Exception('Only controls and fieldset are allowed');
      }
   }
   
   /**
    * 
    * @param type $key
    * @param type $value
    */
   protected function onArrayAccessUnset(&$key, $value) {
      parent::onArrayAccessUnset($key, $value);
      if($value instanceof FormAware) {
         $value->setForm(null);
      }
   }
}