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
      $_closureArgs = null,
      $_scopeObject = null,
      $_closure = null;

   /**
    * 
    * @param \Closure $closure
    * @param object $thisScope
    */
   public function __construct(\Closure $closure, $thisScope, $args = null) {
      $this->_closure = $closure;
      $this->_scopeObject = $thisScope;
      if($args) $this->_closureArgs = $args;
   }
   
   public function setClosureArgs($args) {
      $this->_closureArgs = $args;
   }
   
   public function getClosureArgs() {
      return $this->_closureArgs;
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
         
         $args = $this->getClosureArgs();
         if($args) {
            return call_user_func_array($closure, $args);
         } else {
            return $closure();
         }
      }
   }
}