<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;

class StaticControl extends Control {
   
   public function __construct($name, $value = null, $label = null, $attributes = null) {
      parent::__construct($name, $value, $label, $attributes);
   }

   public function setValue($value) {
      parent::setValue($value);
      $this->setHtml($value);
   }
}