<?php
namespace oxide\base\pattern;
use oxide\base\AbstractClass;

trait SingletonTrait {
   protected static 
      $_t_params = null,   
      $_t_instance = null;
   
   protected function __construct() {}
   
   protected function __clone() {}
   
   protected function __wake() {}
   
   /**
    * 
    * @param type $params
    */
   public static function setConstructParams($params) {
      if(!is_array($params)) {
         $params = [$params];
      }
      
      self::$_t_params = $params;
   }
   
   /**
	 * returns the single instance of this object.
	 */
	final public static function getInstance() {
		if(self::$_t_instance == null) {
         $class = get_called_class();
			self::$_t_instance = AbstractClass::create($class, self::$_t_params);
         self::$_t_params = null;
		}
		return self::$_t_instance;
	}   
}