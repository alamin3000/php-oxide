<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;

/**
 * Description of Factory
 *
 * @author alaminahmed
 */
class Factory {
   
   
   public function __construct() {
      
   }
   
   
   public function createValidator($args) {
      if(is_string($args)) {
         $class = $args;
      } else if(is_array($args)) {
         
      }
      
      
      if($class && class_exists($args)) {
         
      }
   }
}
