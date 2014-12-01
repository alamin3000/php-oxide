<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\std;

interface Cacheable {
   
   /**
    * @return string Renders and returns cached value
    */
   public function cache();
   public function cacheInvalidate();
}