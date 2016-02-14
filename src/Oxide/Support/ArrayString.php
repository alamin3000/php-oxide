<?php
namespace Oxide\Support;

use ArrayObject;
use Closure;
use Oxide\Support\Pattern\ArrayObjectTrait;

/**
 *
 *
 */
class ArrayString extends ArrayObject
{
    use ArrayObjectTrait;

    private
        /**
         * @var \Closure
         */
        $_stringify_callback = null;

    /**
     *
     * @param \Closure $callback
     */
    public function setStringifier(Closure $callback)
    {
        $this->_stringify_callback = $callback;
    }

    /**
     * get current stringify callback.
     *
     * @access public
     * @return \Closure
     */
    public function getStringifier()
    {
        return $this->_stringify_callback;
    }

    /**
     * Replaces current string with the given string.
     *
     * @param string $str
     * @return void
     */
    public function replace($str)
    {
        $this->exchangeArray($str);
    }

    /**
     * __toString function.
     *
     * @return string
     */
    public function __toString()
    {
        $callback = $this->getStringifier();
        if ($callback) {
            return $callback($this);
        } else {
            return implode('', $this->getArrayCopy());
        }
    }
}