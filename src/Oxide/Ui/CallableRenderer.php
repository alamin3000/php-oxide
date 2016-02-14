<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;

class CallableRenderer implements Renderer {
   protected 
      $_callable = null;

   /**
    * 
    * @param callable $callable
    */
   public function __construct(callable $callable) {
      $this->_callable = $callable;
   }

   public function render() {
      $callable = $this->_callable;
      if($callable) {
         return call_user_func($callable);
      }
   }
}