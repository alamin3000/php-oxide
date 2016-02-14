<?php
namespace Oxide\Support\Pattern;

use Closure;

/**
 * Trait the provides default instance functionality
 *
 * Provides ability to set and access default instance for any object using it.
 */
trait SharedInstanceTrait
{

    protected
    /**
     * @var Closure
     */
    static $_t_defaultinstance = null;

    /**
     * Returns the single instance of this object.
     *
     * If you need to simply check if there is default instance, use hasDefaultInstance()
     * This method will create new instance if instance isn't available
     * @return static
     * @throws \Exception
     */
    final public static function sharedInstance()
    {
        if (self::$_t_defaultinstance === null) {
            throw new \Exception('Default Instance not found for: ' . get_called_class());
        }

        if (self::$_t_defaultinstance instanceof Closure) {
            $closure = self::$_t_defaultinstance;
            self::$_t_defaultinstance = $closure();

            if (!self::$_t_defaultinstance instanceof static) {
                throw new \Exception("Shared Instance is not of self kind: " . get_class(self::$_t_defaultinstance));
            }
        }

        return self::$_t_defaultinstance;
    }

    /**
     * Checks if there is any default instance
     *
     * @return bool
     */
    final public static function hasSharedInstance()
    {
        return (self::$_t_defaultinstance != null);
    }

    /**
     * Sets the default instance.
     *
     * The instance must be same object type of the class type
     *
     * @param Closure|object|SharedInstanceTrait $instance
     * @return null
     */
    final public static function setSharedInstance(self $instance)
    {
        self::$_t_defaultinstance = $instance;
    }

    /**
     * Bind a closure into the shared instance so it can be loaded when requested
     *
     * @param Closure $closure
     */
    final public static function bindSharedInstance(Closure $closure)
    {
        self::$_t_defaultinstance = $closure;
    }
}