<?php
namespace oxide\ui\html;


class LinkControl extends Control {
   public function __construct($name, $value = null, $label = null, $data = null, $attributes = null) {
      parent::__construct($name, $value, $label, $data, $attributes);
      $this->setTagName('a');
   }
   
   protected function setName($name) {
      parent::setName($name);
      $this->setAttribute('id', $name);
   }
   
   /**
    * Value of Link Control is href of the link
    * 
    * @param string $value
    */
   public function setValue($value)  {
      parent::setValue($value);
      $this->_attributes['href'] = $value;
   }
   
   /**
    * Label for link control is the inner html of the tag
    * 
    * @param string $str
    */
   public function setLabel($str) {
      parent::setLabel($str);
      $this->setHtml($str);
   }
}