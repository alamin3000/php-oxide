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

class Locale  {
	protected
      $_dictionary = null;
   
   public function __construct() {
      $this->_dictionary = new Dictionary();
   }
   
   /**
    * Translate the given text.
    * 
    * @access public
    * @param mixed $text
    * @return void
    */
   public function translate($text) {
      return $this->_dictionary->get($text, $text);
   }
}