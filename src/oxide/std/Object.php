<?php
namespace oxide\std;

/**
 * A Generic object
 * Supports readonly properties and modification tracking
 */
class Object implements Stringify {
   private
      $_objectId = 0;
   
   private static
      $_objectsCount = 0;
   
   public function __construct() {
      self::$_objectsCount++;
      $this->_objectId = self::$_objectsCount;
   }
   
   /**
    * Get the class reflector
    * @staticvar type $reflector
    * @return ReflectionClass
    */
   public function classReflector() {
      static $reflector = null;
      if($reflector === null) {
         $reflector = new ReflectionClass(get_called_class());
      }
      
      return $reflector;
   }
   
   /**
    * Get the 
    * @staticvar type $dir
    * @return type
    */
   public function classDir() {
      static $dir = null;
      if($dir === null) {
         $dir = realpath(dirname($this->classReflector()->getFileName()));
      }
      
      return $dir;
   }
   
   /**
    * Get the namespace
    * @staticvar type $namespace
    * @return type
    */
   public function classNamespace() {
      static $namespace = null;
      if($namespace === null) {
         $namespace = $this->classReflector()->getNamespaceName();
      }
      
      return $namespace;
   }
   
   /**
    * Create a new class
    * @param type $class
    * @param array $args
    * @return \oxide\std\class
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
   
   public function __toString() {
      return "[Object Id: " . $this->_objectId . "]";
   }
}