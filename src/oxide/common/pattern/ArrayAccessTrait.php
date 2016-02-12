<?php
namespace Oxide\Common\Pattern;

/**
 * Common Array storage/access functionalities
 *
 * This trait provides some useful functionalities to access and manupulate array
 * This trait implements various interfaces related to array:
 * ArrayAccess, Countable
 * In order to take advantages of these, the class using this trait must implement
 * these interfaces in declaration
 */
trait ArrayAccessTrait
{

    protected
        $_t_array_storage = [];

    /**
     *
     * @param array $arr
     */
    public function setArray(array $arr)
    {
        foreach ($arr as $key => $val) {
            $this->offsetSet($key, $val);
        }

        return $this;
    }

    /**
     *
     * @param array $arr
     * @return type
     */
    public function exchangeArray(array $arr)
    {
        $this->onArrayExchange($arr);
        $curr = $this->_t_array_storage;
        $this->_t_array_storage = $arr;

        return $curr;
    }

    /**
     * Get internal array copy
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->_t_array_storage;
    }

    /**
     * Get the internal array reference
     *
     * @return type
     */
    public function &arrayRef()
    {
        return $this->_t_array_storage;
    }

    /**
     * Add $content at the end of the array
     *
     * @param mixed $content
     */
    public function append($content, $offset = null)
    {
        $this->onArrayAccessSet(null, $content);
        if ($offset === null) {
            $this->_t_array_storage[] = $content;
        } else {
            $this->_t_array_storage[$offset] = $content;
        }

        return $this;
    }

    /**
     * Add $content at the begining of the array
     *
     * @param mixed $content
     */
    public function prepend($content, $offset = null)
    {
        $this->onArrayAccessSet(null, $content);
        if ($offset === null) {
            array_unshift($this->_t_array_storage, $content);
        } else {
            $this->_t_array_storage = [
                    $offset => $content
                ] + $this->_t_array_storage;
        }

        return $this;
    }

    /**
     *
     * @param type $value
     * @return type
     */
    public function search($value)
    {
        return array_search($value, $this->_t_array_storage, true);
    }

    /**
     * Insert object.
     *
     *
     * If $offset is given
     * If $index is given, it will inserted at that location
     *
     * @param mixed $value
     * @param mixed $offset
     * @param int $index
     */
    public function insert($value, $offset = null, $index = null)
    {
        $this->onArrayAccessSet($offset, $value);
        if ($index === null) {
            // this is same as normal insert
            $this->_t_array_storage[$offset] = $value; // $offset's null case will be dealt by offsetSet method
        } else {
            if ($offset) {
                $arrvalue = [
                    $offset => $value
                ];
                $this->_t_array_storage = array_slice($this->_t_array_storage, 0, $index,
                        true) + $arrvalue + array_slice($this->_t_array_storage, $index, null, true);
            } else {
                $value = [
                    $value
                ];
                array_splice($this->_t_array_storage, $index, 0, $value);
            }
        }

        return $this;
    }

    /**
     * Uses array_replace_recursive
     *
     * @param array $arr
     */
    public function merge(array $arr)
    {
        $this->_t_array_storage = array_replace_recursive($this->_t_array_storage, $arr);

        return $this;
    }

    /**
     * Indicates if $offset exists
     *
     * Implements ArrayAccess interface
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_t_array_storage[$offset]);
    }

    /**
     * Get object at $offset, if available
     *
     * Implements ArrayAccess interface
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->_t_array_storage[$offset];
    }

    /**
     * Add an object $value at $offset
     *
     * Implements ArrayAccess interface
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->onArrayAccessSet($offset, $value);
        if (is_null($offset)) {
            $this->_t_array_storage[] = $value;
        } else {
            $this->_t_array_storage[$offset] = $value;
        }
    }

    /**
     * Remove the object at $offset
     *
     * Implements ArrayAccess interface
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->_t_array_storage[$offset]) {
            $value = $this->_t_array_storage[$offset];
            $this->onArrayAccessUnset($offset, $value);
            unset($this->_t_array_storage[$offset]);
        }
    }

    /**
     * Returns number of entries in the array
     *
     * Implements Countable interface.
     * Class using this trait should implement Countable in the declaration
     *
     * @return int
     */
    public function count($mode = COUNT_NORMAL)
    {
        return count($this->_t_array_storage, $mode);
    }

    protected function onArrayExchange(&$arr)
    {
    }

    protected function onArrayAccessSet(&$key, $value)
    {
    }

    protected function onArrayAccessUnset(&$key, $value)
    {
    }
}