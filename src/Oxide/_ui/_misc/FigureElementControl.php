<?php
namespace oxide\ui\misc;
use oxide\ui\html\Element;
use oxide\ui\html\ElementControl;
use oxide\ui\html\Control;
use oxide\util\ArrayString;

class FigureElementControl extends ElementControl {
   protected
      $_figcaptionElement = null,
      $_imgElement = null;
   
   /**
    * 
    * @param string $name
    * @param string $value
    * @param string $label
    * @param array $attributes
    */
   public function __construct($name, $value = null, $label = null, $attributes = null) {
      parent::__construct('figure', $name, $value, $label, $attributes);
      
      $caption = new Element('figcaption');
      $img = new Element('img');
      
      $this->_figcaptionElement = $caption;
      $this->_imgElement = $img;
      $this->append($img);
      $this->append($caption);
   }
   
   /**
    * Get the caption element
    * 
    * @return Element
    */
   public function getCaptionElement() {
      return $this->_figcaptionElement;
   }
   
   /**
    * Get the image element
    * 
    * @return Element
    */
   public function getImageElement() {
      return $this->_imgElement;
   }
   
   /**
    * 
    * @param string $value
    */
   public function setValue($value) {
      parent::setValue($value);
   }
   
   protected function onRenderLabel(Control $control, ArrayString $buffer) {
      if($this->getValue())
         parent::onRenderLabel($control, $buffer);
   }
   
   /**
    * @param \oxide\util\ArrayString $buffer
    */
   protected function onPreRender(ArrayString $buffer) {
      parent::onPreRender($buffer); // let the form prepare the control
                  
      if($this->getValue()) {
         $src = $this->getValue();
         $this->_imgElement->setAttribute('src', $src);
         $a = new Element('a', basename($src), array('href' => $src));
         $this->_figcaptionElement->append($a);
      }
   }
}