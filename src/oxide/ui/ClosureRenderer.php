<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;

class ClosureRenderer implements Renderer {
   protected 
      $_scopeObject = null,
      $_closure = null;

   /**
    * 
    * @param \Closure $closure
    * @param object $thisScope
    */
   public function __construct(\Closure $closure, $thisScope) {
      $this->_closure = $closure;
      $this->_scopeObject = $thisScope;
   }

   public function render() {
      $closure = $this->_closure;
      if($closure) {
         $classScope = $this->_scopeObject;
         if($classScope) {
            $closure = $closure->bindTo($classScope);
         } else {
            $closure = $closure->bindTo($this);
         }
         
         return $closure();
      }
   }
}