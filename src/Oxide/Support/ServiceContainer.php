<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */
namespace Oxide\Support;

use Exception;
use ArrayAccess;

/**
 * ServiceContainer class.
 */
class ServiceContainer implements ArrayAccess
{

    protected
        $_aliases = [],
        $_resolvers = [],
        $_instances = [];

    public function __construct()
    {
    }

    /**
     * Binds a resolver to the container
     *
     * @param string $resolvable
     * @param mixed $resolver
     * @param boolean $shared
     */
    public function bind($resolvable, $resolver = null, $shared = true)
    {
        if (is_array($resolvable)) {
            // if resolvable is an array
            // we take the first entry as the resolvable and rest as alias
            $alias = $resolvable;
            $resolvable = array_shift($alias);
        } else {
            $alias = $resolvable;
        }

        if (!$resolver) {
            // if resolver is not given, then we assume $resolvable is also $resolver class
            $resolver = $resolvable;

        } else {

            if (is_object($resolver) && !$resolver instanceof \Closure) {
                // if resolver is an object, (not closure)
                // then we will treat it as an instance
                // so store directly into the container
                // please note, this will also update if there is already instance exits
                $this->_instances[$resolvable] = $resolver;
            }
        }


        $this->_resolvers[$resolvable] = [
            $resolver,
            $shared
        ];
        $this->alias($alias, $resolvable); // store alias
    }

    /**
     * Checks to see if given $resolvable is resolvable
     *
     * It will check through chain if it is string
     * - first check if any instance available for the $resolvable
     * - next checks if any resolver is binded
     * - next checks if it is any alias of other resolver
     * - finally it will check if any class exists with that name
     *
     * @param mixed $resolvable
     * @return boolean
     */
    public function isResolvable($resolvable)
    {
        if (!is_string($resolvable)) {
            return false;
        }

        if (isset($this->_instances[$resolvable]) || // first check for instance
            isset($this->_resolvers[$resolvable]) || // check for resolver
            isset($this->_aliases[$resolvable]) || // check for alias
            class_exists($resolvable, true)
        ) { // check if class
            return true;
        }

        return false;
    }

    /**
     * Attempts to resolve $resolvable by checking alias
     *
     * @param string $resolvable
     * @return string
     */
    protected function resolveResolvable($resolvable)
    {
        // check for alias
        if (isset($this->_aliases[$resolvable])) {
            $resolvable = $this->_aliases[$resolvable];
        }

        // check if resolver exists for $resolvable
        if (isset($this->_resolvers[$resolvable])) {
            return $resolvable;
        }

        return $resolvable;
    }

    /**
     * Add alias
     *
     * @param string|array $alias
     * @param string $resolvable
     * @throws Exception
     */
    public function alias($alias, $resolvable)
    {
        // first make sure $resolvable is already registered
        if (!isset($this->_resolvers[$resolvable])) {
            throw new \Exception("No resolver is registered for: {$resolvable}");
        }

        if (is_array($alias)) {
            foreach ($alias as $name) {
                $this->_aliases[$name] = $resolvable;
            }
        } else {
            $this->_aliases[$alias] = $resolvable;
        }
    }

    /**
     * Resolve function.
     *
     * @access public
     * @param mixed $resolvable
     * @return mixed|string
     * @throws Exception
     */
    public function resolve($resolvable)
    {
        if (is_array($resolvable)) {
            throw new Exception("Can not resolve an array.");
        }

        // get the resolved name
        $resolvable = $this->resolveResolvable($resolvable);

        // first check if already resolved,
        // if so return that instance
        if(isset($this->_instances[$resolvable])) {
            return $this->_instances[$resolvable];
        }

        // check if resolver is found
        if (!isset($this->_resolvers[$resolvable])) {
            $resolver = $resolvable;
            $shared = false;
        } else {
            // now time to resolve
            list ($resolver, $shared) = $this->_resolvers[$resolvable];
        }

        if (is_string($resolver)) {
            // this is a class name
            $instance = $this->instantiate($resolver);
        } else {
            if ($resolver instanceof \Closure) {
                // this is a closure
                $instance = $this->invoke($resolver);
            } else {
                // this is a instance/data
                $instance = $resolver;
            }
        }

        if (!$instance) {
            throw new \Exception('Unable to resolve: ' . $resolvable);
        }

        return $instance;
    }

    /**
     * Remove the resolvable from the container
     *
     * It will remove instance and resolver if avalable, as well as any alias related to it
     *
     * @param string $resolvable
     */
    public function remove($resolvable)
    {
        $resolvable = $this->resolveResolvable($resolvable);

        // remove the resolver if available
        if (isset($this->_resolvers[$resolvable])) {
            unset($this->_resolvers[$resolvable]);
        }

        // removing instance if any
        if (isset($this->_instances[$resolvable])) {
            unset($this->_instances[$resolvable]);
        }

        // remove alias
        $alias = array_keys($this->_aliases, $resolvable);
        if ($alias) {
            foreach ($alias as $key) {
                unset($this->_aliases[$key]);
            }
        }
    }

    /**
     * Invokes given $callable with argument injection from the container
     *
     * @param \oxide\base\callable $callable
     * @return mixed
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function invoke(callable $callable, array $additionalParams = null)
    {
        if (is_string($callable) || $callable instanceof \Closure) {
            $reflection = new \ReflectionFunction($callable);
        } else {
            if (is_array($callable)) {
                list ($class, $method) = $callable;
                $reflection = new \ReflectionMethod($class, $method);
            } else {
                throw new \Exception("Unable to resolve this callable.");
            }
        }

        $args = $this->resolveParamArguments($reflection, $additionalParams);
        return $reflection->invokeArgs($args);
    }

    /**
     *
     * @param \ReflectionFunctionAbstract $function
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function resolveParamArguments(\ReflectionFunctionAbstract $function, array $additionalParams = null)
    {
        $args = [];
        if ($function->getNumberOfParameters() > 0) {
            foreach ($function->getParameters() as $param) {
                $name = $param->getName();
                $class = $param->getClass();
                $instance = null;

                // first attempt to resolve using param name
                if ($additionalParams && isset($additionalParams[$name])) {
                    $instance = $additionalParams[$name];
                } else {
                    if (isset($this[$name])) {
                        $instance = $this[$name];
                    } else {
                        // we weren't able to resolve using the param name
                        // now attempt using class name
                        if ($class) {
                            $className = $class->name;
                            if (isset($this[$className])) {
                                $instance = $this[$className];
                            } else {
                                if ($class->hasMethod('sharedInstance')) {
                                    $instance = $class->getMethod('sharedInstance')->invoke(null);
                                } else {
                                    // unable to resolve the param
                                    // now we will try to instantiate manullay
                                    $instance = $this->instantiate($className, $additionalParams);
                                }
                            }
                        } else {
                            if ($param->isDefaultValueAvailable()) {
                                $instance = $param->getDefaultValue();
                            }
                        }
                    }
                }

                if ($instance === null && !$param->isOptional()) {
                    // unable to resolve required the param.
                    throw new \InvalidArgumentException("Unable to resolve parameter: " . $name);
                }

                // if $instance is found we will match if matches wity class type
                if ($instance && $class && !$instance instanceof $class->name) {
                    throw new \InvalidArgumentException("Argument type mismatch for parameter: " . $name);
                }

                $args[$name] = $instance;
            }
        }

        return $args;
    }

    /**
     * Instanciate a new $class by resolving constructor params from the container
     *
     * Note, this is different than resolve method because it will always attemp to create a new instance of $class,
     * Instead of trying to resolve the $class from the container first.
     * Paramaters will however be resolved using the container + $additionalParams, just as the resolve method.
     *
     * @param string $class
     * @return mixed
     */
    public function instantiate($class, array $additionalParams = null)
    {
        if (!class_exists($class)) {
            return null;
        }

        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $args = $this->resolveParamArguments($constructor, $additionalParams);
            $instance = $reflector->newInstanceArgs($args);
        } else {
            $instance = $reflector->newInstanceWithoutConstructor();
        }

        return $instance;
    }

    /**
     *
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        $this->bind($offset, $value);
    }

    /**
     *
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Check resolver
     *
     * @param type $key
     * @return type
     */
    public function offsetExists($key)
    {
        return $this->isResolvable($this->resolveResolvable($key));
    }

    /**
     * Added support for resolver
     *
     * @param mixed $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->resolve($name);
    }
}