<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\base\Container;

class ResourceContainer extends Container {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   public function __construct($data = null) {
      parent::__construct($data);
      
   }
}