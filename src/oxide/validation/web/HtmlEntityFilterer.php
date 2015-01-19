<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation;
use oxide\validation\Filterer;

class HtmlEntityFilterer implements Filterer {
   protected
      $_flag = ENT_QUOTES;
   
   public function __construct($flag = null) {
      if($flag) $this->_flag = $flag;
   }
   
   public function filter($value) {
      return htmlspecialchars($value, $this->_flag);
   }
}