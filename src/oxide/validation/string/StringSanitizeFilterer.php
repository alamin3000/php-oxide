<?php
namespace oxide\validation;

class StringSanitizeFilterer implements Filterer
{
   private $_flags;

   public function __construct($flags = null)
   {
      // make sure that filter extension is installed
		if(!function_exists('filter_list')) {
			throw new Exception(__CLASS__ . ' requires filter functions in php 5.2 > or PECL');
		}

      $this->_flags = $flags;
   }

   public function filter($value)
   {
      return filter_var($value, \FILTER_SANITIZE_STRIPPED);
   }
}