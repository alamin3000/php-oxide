<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation\web;
use oxide\validation\Filterer;

class Nl2BrFilterer implements Filterer {
   public function filter($value) {
      return nl2br($value);
   }
}