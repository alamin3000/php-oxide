<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\base;
use oxide\base\pattern\ArrayAccessTrait;

/**
 * Dictionary
 * 
 * Standard dictionary object
 * Provides assoicative array interface for storing key/value data
 */
class Dictionary 
   implements \ArrayAccess, \Countable, \IteratorAggregate {
   use ArrayAccessTrait;
   
   /**
    * Construct the dictionary with given $data, if any
    * 
    * @param mixed $data
    */
   public function __construct($data = null) {
      if($data) $this->setArray ($data);
   }
   
   /**
    * 
    * @param type $key
    * @return type
    * @throws \Exception
    */
   public function getRequired($key) {
      if($this->offsetExists($key)) {
         return $this->offsetGet($key);
      } else {
         throw new \Exception('Required key: ' . $key . ' not found.');
      }
   }
   
   /**
    * 
    * @param type $key
    * @param type $default
    * @return type
    */
   public function getOptional($key, $default = null) {
      if($this->offsetExists($key)) {
         return $this->offsetGet($key);
      } else {
         return $default;
      }
   }
   
   /**
    * Overrides to add Dictionary $data param support
    * 
    * @param \oxide\base\Dictionary $data
    * @throws \InvalidArgumentException
    */
   public function setArray($data) {
      if(is_array($data)) {
         $this->_t_array_storage = $data;
      } else if($data instanceof Dictionary) {
         $this->_t_array_storage = $data->toArray();
      } else {
         throw new \InvalidArgumentException('Invalid data passed.');
      }
   }
   
   /**
    * Get the iterator
    * Implementing
    */
   public function getIterator() {
      foreach ($this->_t_array_storage as $item) {
          yield $item;
      }
   }
}