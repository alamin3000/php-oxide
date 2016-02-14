<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */

namespace Oxide\Support\Patter;

trait InjectorTrait
{

    /**
     * Invoke given $callable and attempt to resolve the params argument.
     *
     * All param names will be attempted to match with resolver container.  If
     * matches then
     * @param callable $callable
     * @return type
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function invokeByInjecting(callable $callable, Dictionary $container)
    {
        if (is_string($callable) || $callable instanceof \Closure) {
            $reflection = new \ReflectionFunction($callable);
        } else {
            if (is_array($callable)) {
                list($class, $method) = $callable;
                $reflection = new \ReflectionMethod($class, $method);
            } else {
                throw new \Exception("Unable to resolve this callable.");
            }
        }

        if ($reflection->getNumberOfParameters() > 0) {
            $args = [];
            foreach ($reflection->getParameters() as $param) {
                $name = $param->getName();

                $instance = $container->get($name, null);
                if ($instance === null) {
                    throw new \InvalidArgumentException("Unable to resolve parameter: " . $name);
                }

                $args[$name] = $instance;
            }
        }

        return call_user_func_array([$this, $method], $args);
    }


    /**
     *
     * @param type $class
     */
    protected function instanciateByInjecting($class)
    {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();
        if ($constructor->getNumberOfParameters() > 0) {
            $params = $constructor->getParameters();
            foreach ($params as $param) {
                $paramClass = $param->getClass();
                $paramName = $param->getName();
            }
        }
    }
}