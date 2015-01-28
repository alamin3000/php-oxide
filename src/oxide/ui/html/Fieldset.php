<?php
namespace oxide\ui\html;

/**
 * 
 */
class Fieldset extends Element  {
   public
      $legendTag = null;
   
   protected 
      $_legend = null;


   public function  __construct($legend = null, $attributes = null) {
		parent::__construct('fieldset', null, $attributes);
      $this->setLegend($legend);
	}
   
   public function setLegend($legend) {
      $this->_legend = $legend;
   }
   
   public function getLegend() {
      return $this->_legend;
   }

   /**
    * 
    * @param Form $form
    */
   public function setForm(Form $form = null) {
      // we will apply form value to all entries
      foreach($this->toArray() as $element) {
         if($element instanceof Control || $element instanceof Fieldset) { // this will also include fieldset, since it is also control
            $element->setForm($form);
         }
      }
   }
 
   protected function onRender() {
      if($this->_legend) {
         if(!$this->legendTag) $this->prepend(self::renderTag ('legend', $this->_legend));
         else $this->prepend($this->legendTag->renderContent($this->_legend));
      }
   }
}