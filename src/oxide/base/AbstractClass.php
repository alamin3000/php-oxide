<?php
namespace oxide\base;
use ReflectionClass;


/**
 * AbstractClass
 * 
 * 
 * @package oxide
 * @subpackage base
 */
abstract class AbstractClass {
   private
      $_classDir = null,
      $_reflector = null,
      $_namespace = null;
   /**
    * Get the class reflector
    * @return ReflectionClass
    */
   public function classReflector() {
      if($this->_reflector === null) {
         $this->_reflector = new ReflectionClass(get_called_class());
      }
      
      return $this->_reflector;
   }
   
   /**
    * Get the directory for the class
    * 
    * @return string
    */
   public function classDir() {
      if($this->_classDir === null) {
         $this->_classDir = realpath(dirname($this->classReflector()->getFileName()));
      }
      
      return $this->_classDir;
   }
   
   /**
    * Get the namespace for the class
    * 
    * @return string
    */
   public function classNamespace() {
      if($this->_namespace === null) {
         $this->_namespace = $this->classReflector()->getNamespaceName();
      }
      
      return $this->_namespace;
   }
   
   /**
    * Get the base namespace of the class, if any
    * 
    * @return string
    */
   public function classBaseNamespace() {
      $namespace = $this->classNamespace();
      if($namespace) {
         $parts = explode('\\', $namespace, 2);
         return $parts[0];
      }
      
      return null;
   }
   
   /**
    * 
    * @param string $class
    * @param array $args
    * @return \oxide\base\class
    */
   public static function create($class, array $args = null) {
      $instance = null;
      \oxide\util\Debug::dump($class);
      throw new \Exception();
      if($args) {
         $reflector = new \ReflectionClass($class);
         $instance = $reflector->newInstanceArgs($args);
      } else {
         $instance = new $class();
      }
      
      return $instance;
   }
   
   /**
    * Instanciate a class based on given $args
    * 
    * $args must be one of the three format
    *    object, in this case, it will simply return the object
    *    array, in this case, array must be $class => $params 
    *    string, create new using new $args()
    *    function
    * @param type $args
    * @param type $instanceof
    * @return \oxide\base\args
    */
   public static function instantiate($args, $instanceof = null) {
      if(is_object($args)) {
         $instance = $args;
      } else if(is_array($args)) {
         // create instance using reflection
         // class => args
         list($class, $params) = $args;
         $instance =  self::create($class, $params);
      } else if(is_string($args)) {
         $instance =  new $args();
      } else if($args instanceof \Closure) {
         $instance = $args();
      }
      
      if($instanceof) {
         if(!$instance instanceof $instanceof) {
            throw new \Exception("Instanciated class is not type of $instanceof");
         }
      }
      
      return $instance;
   }
}