<?php
namespace oxide\ui\html;


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
   public function resolveTypeClass($type) {
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
    * @param mixed $type
    * @param mixed $name
    * @param mixed $value (default: null)
    * @param mixed $label (default: null)
    * @param mixed $data (default: null)
    * @param array $attribs (default: null)
    * @return html\Control
    * @throws Exception
    */
   public function create($type, $name, $value = null, $label = null, $data = null, array $attribs = null) {
      $class = $this->resolveTypeClass($type);
	   if(!$class) {
		   throw new \Exception("Control ({$type}) not found, or unable to resolve.");
	   }
	  
		$control = new $class($name, $value, $label, $data);
		if($data) {
			$control->setData($data);
		}
      
		if($attribs) {
			$control->setAttributes($attribs);
		}
		return $control;
   }
}