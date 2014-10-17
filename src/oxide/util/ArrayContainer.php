<?php
namespace oxide\util;

/**
 * Simple Array Container class
 * This object wraps array in an object
 * Provides all basic array access functionalities
 * It also provides various addeded functionalities for accessing and manupulatin the array
 */
class ArrayContainer implements \ArrayAccess, \Countable {
   use pattern\ArrayFunctionsTrait;
   
   public function getIterator() {
      foreach ($this->_t_array_storage as $item) {
          yield $item;
      }
   }
}