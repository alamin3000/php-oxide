<?php
namespace oxide\ui\html;

/**
 * HTML file control class
 * 
 */
class FileControl extends InputControl {   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    * @param type $attrbs
    */
   public function __construct($name, $value = null, $label = null, $attrbs = null) {
      parent::__construct('file', $name, $value, $label, $attrbs);
      $this->_info = basename($value);
   }
      
   /**
    * 
    * @param type $value
    * @throws \Exception
    */
   public function setValue($value) {
      $newval = null;
      if(is_array($value)) { // this happens when for is trying to set value
         if(isset($value['name']))
            $newval = $value['name'];
         else // this is odd case, shouldn't happen
            throw new \Exception('In correct value is being set for value');
            
      } else {
         $newval = $value;
      }
      parent::setValue($newval);
   }
   
   /**
    * 
    * @param \oxide\ui\html\Form $form
    */
   public function setForm(Form $form = null) {
      parent::setForm($form);
      
      if($form) {
         // we will also need to modify form attribute
         $form->enctype="multipart/form-data";
      }
   }
}