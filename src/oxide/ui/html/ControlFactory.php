<?php
namespace oxide\ui\html;
use oxide\helper\_html;


abstract class ControlFactory {
   protected static
      $controls = [
          
      ];
   
   
   public static function create($name, $type, $value = null, $label = null, $options = null, $attributes = null) {
      $control = null;
      if($type == 'button' || $type == 'submit') {
         $control = new ButtonControl($type, $name, $value, $label);
      }else if(isset(_html::$inputTypes[$type])) {
         $control = new InputControl($type, $name, $value, $label);
      } else if($type == 'textarea') {
         $control = new TextareaControl($name, $value, $label);
      } else if($type == 'select') {
         $control = new SelectControl($name, $value, $label, $options);
      } 
      
      return $control;
   }
   
   public static function setup(Form $form, array $definitions) {
      
      foreach($definitions as  $def) {
         $label = (isset($def['label'])) ? $def['label'] : null;
         $value = (isset($def['value'])) ? $def['value'] : null;
         $attributes = (isset($def['attributes'])) ? $def['attributes'] : null;
         
      }
   }
}