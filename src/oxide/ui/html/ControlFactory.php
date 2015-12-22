<?php
namespace oxide\ui\html;
use oxide\base\ReflectingClass;

class ControlFactory {
   
   protected
      $_typeMap = [];
   

   public function __construct() {
      
   }
   
   /**
    * 
    * @param string $type
    * @param string $namespace
    */
   public function registerType($type, $namespace) {
      $this->_typeMap[$type] = $namespace;
   }
   
   /**
    * 
    * @param string $type
    * @return string|null
    */
   public function resolveType($type) {
      if(isset($this->_typeMap[$type])) {
         $namespace = $this->_typeMap[$type];
      } else {
         // use the default oxide type
         $namespace = '\\oxide\\ui\\html\\';
      }
      
      $class = ucfirst($type) . 'Control';
      $fullclass = "{$namespace}{$class}";
      
      if(class_exists($fullclass)) {
         return $fullclass;
      } else {
         return null;
      }
   }
   
   
   /**
    * create function.
    * 
    * @access public
    * @static
    * @param string $type
    * @param string $name
    * @param string $value (default: null)
    * @param string $label (default: null)
    * @param mixed $data (default: null)
    * @param array $attribs (default: null)
    * @return html\Control
    * @throws Exception
    */
   public function create($type, $name, $value = null, $label = null, $data = null, array $attribs = null) {
      $class = $this->resolveType($type);
	   if(!$class) {
		   throw new \Exception("Control ({$type}) not found, or unable to resolve.");
	   }
	  
      $control = ReflectingClass::instantiate($class, [$name, $value, $label, $data, $attribs]);
		return $control;
   }
   
   /**
    * 
    * @param \oxide\base\Dictionary $dictionary
    * @return type
    */
   public function createFromArray($name, $arr) {
      $type = isset($arr['type']) ? $arr['type'] : 'text';
      $label = isset($arr['label']) ? $arr['label'] : null;
      $data = isset($arr['data']) ? $arr['data'] : null;
      $attribs = isset($arr['attributes']) ? $arr['attributes'] : null;
      $value = isset($arr['value']) ? $arr['value'] : null;
      
      
      return $this->create($type, $name, $value, $label, $data, $attribs);
   }
}