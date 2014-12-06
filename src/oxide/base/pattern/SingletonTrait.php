<?php
namespace oxide\base\pattern;

trait SingletonTrait 
{
   protected static 
        $_t_instance = null;
   
   protected function __construct() {}
   
   protected function __clone() {}
   
   protected function __wake() {}

   /**
	 * returns the single instance of this object.
	 */
	final public static function getInstance()
	{
		if(self::$_t_instance == null) {
			self::$_t_instance = new static();
		}
		return self::$_t_instance;
	}   
}