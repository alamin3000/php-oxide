<?php
namespace oxide\ui\html;


abstract class ControlFactory {
   protected static
      $controls = ['input', 'textarea', 'select', 'button'];

   public static function create($type, $name, $value = null, $label = null, $data = null, array $attribs = null) {
	   $class = ucfirst($type) . 'Control';
	   if(!class_exists($class)) {
		   throw new \Exception("Control ({$type}) not found using class: {$class}");
	   }
	  
		$control = new $class($name, $value, $label, $data, $attribs);
		return $control;
   }
}