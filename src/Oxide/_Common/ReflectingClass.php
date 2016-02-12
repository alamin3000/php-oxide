<?php
namespace Oxide\Common;

use ReflectionClass;
use Closure;
use Exception;

/**
 * AbstractClass
 *
 *
 * @package oxide
 * @subpackage base
 */
abstract class ReflectingClass
{
    private
        $_classDir = null,
        $_reflector = null,
        $_namespace = null;

    /**
     * Get the class reflector
     * @return ReflectionClass
     */
    public function classReflector()
    {
        if ($this->_reflector === null) {
            $this->_reflector = new ReflectionClass(get_called_class());
        }

        return $this->_reflector;
    }

    /**
     * Get the directory for the class
     *
     * @return string
     */
    public function classDir()
    {
        if ($this->_classDir === null) {
            $this->_classDir = realpath(dirname($this->classReflector()->getFileName()));
        }

        return $this->_classDir;
    }

    /**
     * Get the namespace for the class
     *
     * @return string
     */
    public function classNamespace()
    {
        if ($this->_namespace === null) {
            $this->_namespace = $this->classReflector()->getNamespaceName();
        }

        return $this->_namespace;
    }

    /**
     * Get the base namespace of the class, if any
     *
     * @return string
     */
    public function classBaseNamespace()
    {
        $namespace = $this->classNamespace();
        if ($namespace) {
            $parts = explode('\\', $namespace, 2);
            return $parts[0];
        }

        return null;
    }

    /**
     * Invoke internal method (with given args) with signature validation
     *
     * @param type $method
     * @param array $args
     * @throws \Exception
     */
    protected function invokeMethod($method, array $args = null)
    {
        if (!$method instanceof \ReflectionFunctionAbstract) {
            $method = self::reflectCallable($method);
        }

        // now we will validate the function with given $args
        $argsCount = ($args === null) ? 0 : count($args);
        if ($argsCount < $method->getNumberOfRequiredParameters()) {
            throw new \InvalidArgumentException("Invalid Param count. (less than required)");
        }

        if ($argsCount > $method->getNumberOfParameters()) {
            throw new \InvalidArgumentException("Invalid Param count. (more than allowed)");
        }


        return $method->invokeArgs($this, $args);
    }


    /**
     *
     * @param \oxide\base\ReflectionFunctionAbstract $method
     * @param array $args
     * @throws \InvalidArgumentException
     */
    protected function validateMethod(ReflectionFunctionAbstract $method, array $args = null)
    {


    }


    /**
     * Attempt to get reflection for given $callable
     *
     * @param callable $callable
     * @return \ReflectionMethod|null
     */
    public static function reflectCallable(callable $callable)
    {
        $reflection = null;
        if (is_string($callable) || $callable instanceof \Closure) {
            $reflection = new \ReflectionFunction($callable);
        } else {
            if (is_array($callable)) {
                list($class, $method) = $callable;
                $reflection = new \ReflectionMethod($class, $method);
            }
        }

        return $reflection;
    }

    /**
     * Attempt to create an instance of $class with given $args as argument list
     *
     * @param string $class
     * @param array $args
     * @return \oxide\base\class
     */
    public static function create($class, array $args = null, $instanceof = null)
    {
        $instance = null;

        if (!$class instanceof ReflectionClass) {

        } else {
            if (is_string($class)) {

            } else {
                if ($class instanceof Closure) {

                } else {
                    if (is_object($class)) {
                        $instance = $class;
                    }
                }
            }
        }

        if ($instance === null) {

        }

        if ($args) {
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
    public static function instantiate($args, $params = null, $instanceof = null)
    {
        if (is_object($args)) {
            $instance = $args;
        } else {
            if (is_array($args)) {
                // create instance using reflection
                // class => args
                list($class, $params) = each($args);
                $instance = self::create($class, $params);
            } else {
                if (is_string($args)) {
                    $instance = self::create($args, $params);
                } else {
                    if ($args instanceof \Closure) {
                        $instance = call_user_func_array($args, $params);
                    }
                }
            }
        }

        if ($instanceof) {
            if (!$instance instanceof $instanceof) {
                throw new Exception("Instanciated class is not type of $instanceof");
            }
        }

        return $instance;
    }

    public static function generateClassName($name, $namespace = null)
    {
        $classname = ucfirst($name);
        if ($namespace) {
            $classname = trim($namespace, '\\') . "\{$classname}";
        }

        return $classname;
    }

    public static function generateClassFile($name, $dir = null)
    {
        $class = ucfirst($name);
    }
}