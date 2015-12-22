<?php
namespace oxide\ui\html;


class ControlFactory {
   
   protected
      $_types = [],
      $_typeMap = [];
   

   public function __construct() {
   }
   
   /**
    * 
    * @param string $type
    * @return string|null
    */
   public function resolveType($type) {
      if(isset($this->_types[$type])) {
         $var = $this->_types[$type];
      } else {
         // use the default oxide type
         $var = '\\oxide\\ui\\html\\';
      }
      
      if(is_string($var)) {
         $namespace = $var;
         $class = ucfirst($type) . 'Control';
         $fullclass = "{$namespace}\\{$class}";

         if(class_exists($fullclass)) {
            return $fullclass;
         } else {
            return null;
         }
      } else if($var instanceof \Closure) {
         // this is a closure
         return $var;
      } else {
         return null;
      }
   }
   
   
   /**
    * 
    * @param string $type
    * @param string $namespace
    */
   public function registerNamespace($type, $namespace) {
      $this->_types[$type] = $namespace;
   }
   
   /**
    * Register a clsoure
    * 
    * @param string $type
    * @param \Closure $closure
    */
   public function registerClosure($type, \Closure $closure) {
      $this->_types[$type] = $closure;
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
	  
      if(is_string($class)) {
         $control = new $class($name, $value, $label, $data, $attribs);
      } else if($class instanceof \Closure) {
         $control = $class($type, $name, $value, $label, $data, $attribs);
         if(!$control instanceof Control) {
            throw new Exception('Closure must return an instance of Control');
         }
      } else {
         throw new Exception('Invalid');
      }
      
		
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