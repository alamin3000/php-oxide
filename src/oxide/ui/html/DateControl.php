<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;

class DateControl extends InputControl {
   public function __construct($name, $value = null, $label = null) {
      parent::__construct('date', $name, $value, $label);
   }
}