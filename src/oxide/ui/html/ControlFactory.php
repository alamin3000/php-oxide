<?php
namespace oxide\ui\html;


abstract class ControlFactory {
   public
      $definitions = [
         'button' => '\\oxide\\ui\\html\\ButtonControl',
         'checkbox' => '\\oxide\\ui\\html\\CheckboxControl',
         'chkgroup' => '\\oxide\\ui\\html\\CheckboxGroupControl',
         'color' => '\\oxide\\ui\\html\\ColorControl',
         'date' => '\\oxide\\ui\\html\\DateControl'
         
      ];
   
   
   protected static
      $controls = ['input', 'textarea', 'select', 'button'];

   public static function create($type, $name, $value = null, $label = null, $data = null, array $attribs = null) {
	   $class = ucfirst($type) . 'Control';
	   $namespace = '\\oxide\\ui\\html\\';
	   $fullclass = "{$namespace}{$class}";
	   if(!class_exists($fullclass)) {
		   throw new \Exception("Control ({$type}) not found using class: {$class}");
	   }
	  
		$control = new $fullclass($name, $value, $label);
		if($data) {
			$control->setData($data);
		}
		if($attribs) {
			$control->setAttributes($attribs);
		}
		return $control;
   }
}