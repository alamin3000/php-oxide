<?php
namespace oxide\std;
use ReflectionClass;


/**
 * Command Controller
 * 
 * implements Command to comply with front controller engines specification
 * provides command execution life cycle events
 * 
 * @package oxide
 * @subpackage http
 */
abstract class AbstractClass {
   public static function initialize() {
      
   }
   
   /**
    * Get the class reflector
    * @staticvar type $reflector
    * @return ReflectionClass
    */
   public static function classReflector() {
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
   public static function classDir() {
      static $dir = null;
      if($dir === null) {
         $dir = realpath(dirname(self::classReflector()->getFileName()));
      }
      
      return $dir;
   }
   
   /**
    * Get the namespace
    * @staticvar type $namespace
    * @return type
    */
   public static function classNamespace() {
      static $namespace = null;
      if($namespace === null) {
         $namespace = self::classReflector()->getNamespaceName();
      }
      
      return $namespace;
   }
}