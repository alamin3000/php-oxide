<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\helper;
use oxide\http\Context;

class HelperAbstract {
   use \oxide\base\pattern\SingletonTrait;
   
   protected
      $_context = null;
   
   public function setContext(Context $context) {
      $this->_context= $context;
   }
   
   public function getContext() {
      return $this->_context;
   }
}