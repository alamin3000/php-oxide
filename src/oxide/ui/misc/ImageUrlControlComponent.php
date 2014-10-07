<?php
namespace oxide\ui\misc;
use oxide\validation\ValidationComponent;
use oxide\ui\html\InputControl;
use oxide\validation\ValidationResult;
use oxide\validation\UriExistsValidator;
use oxide\ui\html\Element;
use oxide\http\Request;

class ImageUrlControlComponent extends InputControl implements ValidationComponent
{
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    * @param type $attrbs
    */
   public function __construct($name, $value = null, $label = null, $attrbs = null)
   {
      parent::__construct('text', $name, $value, $label, $attrbs);
   }
   
   public function setForm(\oxide\ui\html\Form $form = null)
   {
      parent::setForm($form);
      if($form) {
         $form->getValidationProcessor()->addValidationComponent($this, $this->getName());
      }
   }
   
   
   /**
    * 
    * @param type $value
    * @return type
    */
   public function filter($value)
   {
      return $value;
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return type
    */
   public function validate($value, ValidationResult &$result = null)
   {
      $host = Request::currentRequest()->getAbsoluteServerURL();
      $validator = new UriExistsValidator($host);
      $value = $validator->validate($value, $result);
      
      return $value;
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return type
    */
   public function process($value, ValidationResult &$result = null)
   {
      return $value;
   }
}