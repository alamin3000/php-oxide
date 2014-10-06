<?php
namespace oxide\ui\html;


class LinkControl extends Control
{
   public function __construct($name, $value = null, $label = null, $attributes = null) {
      parent::__construct('a', $name, $value, $label, $attributes);
   }
   
   /**
    * Value of Link Control is href of the link
    * 
    * @param string $value
    */
   public function setValue($value)  {
      parent::setValue($value);
      $this->href = $value;
   }
   
   /**
    * Label for link control is the inner html of the tag
    * 
    * @param string $str
    */
   public function setLabel($str) {
      parent::setLabel($str);
      $this->html($str);
   }
}