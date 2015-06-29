<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */
namespace oxide\base;

/**
 * Container class.
 * Adds lazy loading support to Dictionary.
 * Object/resource can be assigned using closure for lazy loading when accessed for the first time
 * @note key/names will be stored in case insensitive
 */
class Container extends Dictionary {
   protected
      $_resolvers = [];
   
   /**
	 * Bind a closure to the container for given $name.
	 * 
	 * @param string|array $names If array is passed, same closure will be used
	 * @param \Closure $closure
	 * @return void
	 */
	public function addResolver($names, \Closure $closure) {
      if(is_array($names)) {
         foreach($names as $name) {
            $this->_resolvers[$name] = $closure;
         }
      } else {
         $this->_resolvers[$names] = $closure;
      }
	}
   
   /**
    * 
    * @param type $name
    * @return type
    */
   public function getResolver($name) {
      return $this->_resolvers[$name];
   }
   
   /**
    * 
    * @param type $name
    * @return type
    */
   public function hasResolver($name) {
      return isset($this->_resolvers[$name]);
   }
   
   /**
	 * resolve function.
	 * 
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function resolve($name) {
		if(!isset($this->_resolvers[$name])) {
         return null;
      }
		
      $closure = $this->_resolvers[$name];
		$object = $closure($this, $name);
		return $object;
	}

   /**
    * 
    * @param \oxide\base\callable $callable
    * @return type
    * @throws \Exception
    * @throws \InvalidArgumentException
    */
   public function invoke(callable $callable) {
      if(is_string($callable) || $callable instanceof \Closure) {
         $reflection = new \ReflectionFunction($callable);
      } else if(is_array($callable)) {
         list($class, $method) = $callable;
         $reflection = new \ReflectionMethod($class, $method);
      } else {
         throw new \Exception("Unable to resolve this callable.");
      }
      
      $args = $this->resolveParamArguments($reflection);

      return call_user_func_array($callable, $args);
   }
   
   /**
    * 
    * @param \ReflectionFunctionAbstract $function
    * @return type
    * @throws \InvalidArgumentException
    */
   protected function resolveParamArguments(\ReflectionFunctionAbstract $function) {
      $args = null;
      if($function->getNumberOfParameters() > 0) {
         $args = [];
         foreach($function->getParameters() as $param) {
            $name = $param->getName();
            
            $instance = isset($this[$name]) ? $this[$name] : null;
            if($instance === null) {
               throw new \InvalidArgumentException("Unable to resolve parameter: " . $name);
            }

            $args[$name] = $instance;
         }
      }
      
      return $args;
   }
   
   /**
    * Instanciate a new $class by resolving constructor params
    * 
    * @param string $class
    */
	public function instanciate($class) {
	   $reflector = new \ReflectionClass($class);
	   $constructor = $reflector->getConstructor();
	   if($constructor) {
         $args = $this->resolveParamArguments($constructor);
         $instance = $reflector->newInstanceArgs($args);
      } else {
         $instance = $reflector->newInstanceWithoutConstructor();
      }
      
      return $instance;
   }

   /**
    * Check resolver
    * 
    * @param type $key
    * @return type
    */
	public function offsetExists($key) {
		return parent::offsetExists($key) || $this->hasResolver($key);
	}
   	
   /**
    * Added support for resolver
    * 
    * @param type $key
    * @return type
    */
	public function offsetGet($key) {
		if(parent::offsetExists($key)) {
			return parent::offsetGet($key);	
		}
      
		$val = $this->resolve($key);
		if($val) {
			$this->offsetSet($key, $val);
		}
		
		return $val;
	}
}