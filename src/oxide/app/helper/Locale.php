<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\base\Dictionary;

class Locale extends Dictionary {
   public function translate($text) {
      return $this->get($text, $text);
   }
}