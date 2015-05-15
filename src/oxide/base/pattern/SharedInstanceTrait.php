<?php
namespace oxide\base\pattern;

/**
 * Trait the provides default instance functionality
 * 
 * Provides abiity to set and access default instance for any object using it.
 */
trait SharedInstanceTrait {
   protected static
        $_t_defaultinstance = null;

   /**
	 * Returns the single instance of this object.
    * 
    * If you need to simply check if there is default instance, use hasDefaultInstance()
    * This method will create new instance if instance isn't available
    * @return self
	 */
	final public static function sharedInstance() {
		if(self::$_t_defaultinstance == null) {
			throw new \Exception('Default Instance not found for: ' . get_called_class());
		}
		
		if(self::$_t_defaultinstance instanceof \Closure) {
			$closure = self::$_t_defaultinstance;
			self::$_t_defaultinstance = $closure();
		}
		
		if(!self::$_t_defaultinstance instanceof self) {
			throw new \Exception("Shared Instance is not of self kind: " . get_class(self::$_t_defaultinstance));
		}
		
		return self::$_t_defaultinstance;
	}
   
   /**
    * Checks if there is any default instance
    * @return bool
    */
   final public static function hasSharedInstance() {
      return (self::$_t_defaultinstance != null);
   }
   
   /**
    * Sets the default instance.
    * 
    * The instance must be same object type of the class type
    * @param object $instance
    * @return null
    */
   final public static function setSharedInstance($instance) {
      self::$_t_defaultinstance = $instance;
   }
}