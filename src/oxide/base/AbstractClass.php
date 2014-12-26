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
   
   public function classBaseNamespace() {
      $namespace = $this->classNamespace();
      if($namespace) {
         $base = explode('\\', $namespace, 2);
         return $base;
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
      
      if($args) {
         $reflector = new \ReflectionClass($class);
         $instance = $reflector->newInstanceArgs($args);
      } else {
         $instance = new $class();
      }
      
      return $instance;
   }
}