<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;
use oxide\base\Container;

class ValidationContainer extends Container {
   public
     $breakOnFirstError = true;
   
   
   public function performValidation(array $values, Result &$result = null) {
      if(!$result) $result = new Result();
      $break = $this->breakOnFirstError;
      $data = [];
      foreach($values as $key => $value) {
         if(isset($this[$key])) {
            $result->currentOffset = $key;
            $component = $this[$key];
            $filtered = $component->filter($value);
            $component->validate($filtered, $result);
            if($result->isValid()) {
               $processed = $component->process($filtered, $result);
               if($result->isValid()) {
                  $data[$key] = $processed;
               }
            }
            $result->currentOffset = null;
            if(!$result->isValid() && $break) {
               break;
            }
         }
      }
      
      if($result->isValid())
         return $data;
      else
         return NULL;
   }
}