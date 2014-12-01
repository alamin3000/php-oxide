<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\std;
use oxide\util\pattern\ArrayFunctionsTrait;

/**
 * Dictionary
 * 
 * Standard dictionary object
 * Provides assoicative array interface for storing key/value data
 */
class Dictionary implements \ArrayAccess, \Countable, \IteratorAggregate {
   use ArrayFunctionsTrait;
   
   public function __construct($data = null) {
      
      
      if($data) $this->setData ($data);
   }
   
   public function setData($data) {
      if(is_array($data)) {
         $this->_t_array_storage = $data;
      } else if($data instanceof Dictionary) {
         $this->_t_array_storage = $data->toArray();
      } else {
         throw new \InvalidArgumentException('Invalid data passed.');
      }
   }
   
   public function getIterator() {
      foreach ($this->_t_array_storage as $item) {
          yield $item;
      }
   }
}