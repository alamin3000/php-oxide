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

class Collection 
   implements \ArrayAccess, \Countable , \IteratorAggregate {   
   use ArrayAccessTrait;
   
   public function __construct(array $data = null) {
      if($data) $this->exchangeArray($data);
   }
   
   /**
	 * getIterator function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getIterator() {
		foreach ($this->_t_array_storage as $offset => $item) {
         yield $offset => $item;
      }
	}
}