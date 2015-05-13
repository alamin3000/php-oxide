<?php
namespace oxide\base\pattern;

trait ResolverTrait {
	protected       
   	$_t_defaultResolver = null,
   	$_t_resolvers = [];
   
	/**
	 * Bind a closure to the container for given $name.
	 * 
	 * @param string $name
	 * @param \Closure $closure
	 * @return void
	 */
	public function addResolver($name, \Closure $closure) {
		$this->_t_resolvers[$name] = $closure;
	}
	
	
	/**
	 * hasResolver function.
	 * 
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function hasResolver($name) {
		return isset($this->_t_resolvers[$name]);
	}
	
	/**
	 * bindDefault function.
	 * 
	 * @access public
	 * @param \Closure $closure
	 * @return void
	 */
	public function setDefaultResolver(\Closure $closure) {
		$this->_t_defaultResolver = $closure;
	}
	
	
	/**
	 * getDefaultBind function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getDefaultResolver() {
		return $this->_t_defaultResolver;
	}
   
	/**
	 * resolve function.
	 * 
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function resolve($name) {
		$closure = null;
		if(isset($this->_t_resolvers[$name])) {
			$closure = $this->_t_resolvers[$name];
		} else if($this->_t_defaultResolver) {
			$closure = $this->_t_defaultResolver;
		} else {
			return null;
		}
		
		$object = $closure($this, $name);
		return $object;
	}
}