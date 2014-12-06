<?php
namespace oxide\base\pattern;

/**
 * Trait the provides default instance functionality
 * 
 * Provides abiity to set and access default instance for any object using it.
 */
trait DefaultInstanceTrait
{
   protected static
        $_t_defaultinstance = null;

   /**
	 * Returns the single instance of this object.
    * 
    * If you need to simply check if there is default instance, use hasDefaultInstance()
    * This method will create new instance if instance isn't available
    * @return object
	 */
	final public static function defaultInstance()
	{
		if(self::$_t_defaultinstance == null) {
			self::$_t_defaultinstance = new static();
		}
		return self::$_t_defaultinstance;
	}
   
   /**
    * Checks if there is any default instance
    * @return bool
    */
   final public static function hasDefaultInstance()
   {
      return (self::$_t_defaultinstance != null);
   }
   
   /**
    * Sets the default instance.
    * 
    * The instance must be same object type of the class type
    * @param object $instance
    * @return null
    */
   final public static function setDefaultInstance($instance)
   {
      if(!is_null($instance) && 
         !is_a($instance, get_called_class())) {
         throw new \Exception('Given instance is not a class type of: ' . get_called_class());
      }
      
      self::$_t_defaultinstance = $instance;
   }
}