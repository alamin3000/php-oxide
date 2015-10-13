<?php
namespace oxide\app\helper;
use oxide\base\pattern\ExtendableTrait;
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

class Helper {
   use ExtendableTrait;
   
   
   
   public function __get($name) {
      if(isset($this->_t_extendedObjects[$name])) {
         return $this->_t_extendedObjects[$name];
      }
   }
   
   public function __call($name, $arguments) {
      return $this->invokeExtended($name, $arguments);
   }
}