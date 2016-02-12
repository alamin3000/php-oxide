<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */
namespace Oxide\Common;

use Oxide\Common\Pattern\ArrayObjectExtendTrait;
use Oxide\Util\FileParser;
use ArrayObject;
use Oxide\Common\Pattern\ObservableTrait;

/**
 * Dictionary
 *
 * Standard dictionary object
 * Provides assoicative array interface for storing key/value data
 */
class Dictionary extends ArrayObject
{
    use ObservableTrait, ArrayObjectExtendTrait;

    /**
     * Construct the dictionary with given $data, if any
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        if ($data) {
            $this->exchangeArray($data);
        }
    }

    /**
     * Create a new dictionary from the given file
     *
     * Uses shared FileParser to parse the given file into dictionary
     * @see self::loadFromFile
     * @param string $file
     * @return static
     */
    static public function createFromFile($file)
    {
        $dictionary = new Dictionary();
        $dictionary->loadFromFile($file);

        return $dictionary;
    }

    /**
     * Load content from file and merge the contents into current dictionary
     *
     * @param string $file
     */
    public function loadFromFile($file)
    {
        $parser = FileParser::sharedInstance();
        $data = $parser->parse($file);

        if ($data) {
            $this->merge($data);
        }
    }

    /**
     * Get value using key path.
     *
     *
     * @param string|array $keys
     * @param mixed $default
     * @param string $separator
     * @return mixed
     */
    public function getDeepValue($keys, $default = null, $separator = '.')
    {
        if (!is_array($keys)) {
            $keys = explode($separator, $keys);
        }

        if (empty($keys)) {
            return null;
        }

        $var = $this;
        foreach ($keys as $key) {
            if (!isset($var[$key])) {
                return $default;
            }

            $var = $var[$key];
        }

        return $var;
    }


    /**
     * get value for the provided $key, if not found $default will be returned.
     *
     * @access public
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getValue($key, $default = null)
    {
        if (!isset($this[$key])) {
            return $default;
        }

        return $this[$key];
    }

    /**
     * Get tuple (array) of values for provided $keys
     *
     * @param array $keys
     * @param array|null $defaults
     * @return array
     */
    public function getTuple(array $keys, array $defaults = null)
    {
        $values = [];
        foreach ($keys as $offset => $key) {
            if (!isset($this[$key])) {
                if ($defaults) {
                    $values[] = $defaults[$offset];
                } else {
                    $values[] = $defaults;
                }
            } else {
                $values[] = $this[$key];
            }
        }

        return $values;
    }


    public function set($key, $value = null)
    {
        if (is_array($key)) {
            //
        }
    }
}