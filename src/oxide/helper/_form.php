<?php
namespace oxide\helper;
use oxide\ui\html;
use oxide\std\AbstractClass;

abstract class _form {
   
   
   public static function create_control($name, $type, $value = null, $label = null, $options = null, $attributes = null) {
      $control = null;
      if($type == 'button' || $type == 'submit') {
         $control = new html\ButtonControl($type, $name, $value, $label);
      }else if(isset(_html::$inputTypes[$type])) {
         $control = new html\InputControl($type, $name, $value, $label);
      } else if($type == 'textarea') {
         $control = new html\TextareaControl($name, $value, $label);
      } else if($type == 'select') {
         $control = new html\SelectControl($name, $value, $label, $options);
      } 
      
      return $control;
   }
   
   public static function create_form($name, $method, $action, $attributes) {
      return new html\Form($method, $action, $name);
   }
   
   /**
    * Setups the given $form using the given $defs
    * 
    * $defs = [
    *    'controlname' => [
    *       'type' => 'text',
    *       'value' => 'Value',
    *       'label' => 'Label for control',
    *       'options' => ['additional options, usally for select control'],
    *       'attributes' => ['attribute1' => 'value1']
    *       'required' => true,
    *       'validations' => [
    *          'filters' => [
    *             'FilterClassName' => null
    *          ],
    * 
    *          'validators' => [
    *             'ValidatorClassName' => ['args1', 'args2']
    *          ],
    * 
    *          'processors' => [
    *             'ProcessorClassName' => ['args']
    *          ]
    *       ]
    *    ]
    * ];
    * @param \oxide\ui\html\Form $form
    * @param array $defs
    * @throws \Exception
    */
   public static function setup_form(html\Form $form, array $defs) {
      $vprocessor = $form->getValidationProcessor();
      $requireds = [];
      foreach($defs as $name => $def) {
         $type = isset($def['type']) ? $def['type'] : null;
         $value = isset($def['value']) ? $def['value'] : null;
         $label = isset($def['label']) ? $def['label'] : null;
         $options = isset($def['options']) ? $def['options'] : null;
         $attributes = isset($def['attributes']) ? $def['attributes'] : null;
         $control = self::create_control($name, $type, $value, $label, $options, $attributes);
         if(!$control) {
            throw new \Exception('Unable to create control for ' . $name);
         }

         if(isset($def['required']) && $def['required']) {
            $requireds[] = $name;
         }

         if(isset($def['validation'])) {
            $validation = $def['validation'];
            if(isset($validation['filters'])) {

            }

            if(isset($validation['validators'])) {
               $validators = $validation['validators'];
               $vcontainer = $vprocessor->getValidatorContainer();
               foreach($validators as $class => $args) {
                  $instance = AbstractClass::create($class, $args);
                  if($instance) {
                     $vcontainer->add($instance, $name);
                  }
               }
            }
         }

         $form->addControl($control);               
      }

      $vprocessor->setRequired($requireds);
   }
}