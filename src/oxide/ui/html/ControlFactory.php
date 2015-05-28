<?php
namespace oxide\ui\html;


abstract class ControlFactory {
   /**
    * create function.
    * 
    * @access public
    * @static
    * @param mixed $type
    * @param mixed $name
    * @param mixed $value (default: null)
    * @param mixed $label (default: null)
    * @param mixed $data (default: null)
    * @param array $attribs (default: null)
    * @return void
    */
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