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
   protected 
      $_name = null,
      $_namespace = null,
      $_dir = null;
   
   public function __construct() {
      $reflector = new ReflectionClass(get_called_class());
     	$dir = realpath(dirname($reflector->getFileName()));
      $this->_name = $reflector->getName();
      $this->_dir = $dir;
      $this->_namespace = $reflector->getNamespaceName();
	}
   
   /**
    * 
    * @return type
    */
   public function getName() {
      return $this->_name;
   }
   
   /**
    * 
    * @return type
    */
   public function getNamespace() {
      return $this->_namespace;
   }
   
   /**
    * 
    * @return type
    */
   public function getDirectory() {
      return $this->_dir;
   }
   
   /**
    * Returns the given $path relative to the current directory
    * @param string $path
    * @return string
    */
   public function getDirectoryPath($path) {
      return $this->getDirectory() . '/' . $path;
   }
}