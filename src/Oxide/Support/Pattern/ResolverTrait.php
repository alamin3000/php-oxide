<?php
namespace Oxide\Support\Pattern;

trait ResolverTrait
{
    protected
        $_t_resolvers = [];

    /**
     * Bind a closure to the container for given $name.
     *
     * @param string $name
     * @param \Closure $closure
     * @return void
     */
    public function addResolver($name, \Closure $closure)
    {
        $this->_t_resolvers[$name] = $closure;
    }

    /**
     * hasResolver function.
     *
     * @access public
     * @param mixed $name
     * @return void
     */
    public function hasResolver($name)
    {
        return isset($this->_t_resolvers[$name]);
    }


    /**
     * resolve function.
     *
     * @access public
     * @param mixed $name
     * @return void
     */
    public function resolve($name)
    {
        if (!isset($this->_t_resolvers[$name])) {
            return null;
        }

        $closure = $this->_t_resolvers[$name];
        $object = $closure($this, $name);
        return $object;
    }
}