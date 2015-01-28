<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;

class RadioControl extends InputControl {
   public function __construct($name, $value = null, $label = null) {
      parent::__construct('radio', $name, $value, $label);
   }
}