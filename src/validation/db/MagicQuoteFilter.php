<?php
namespace oxide\validation;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MagicQuoteFilter implements Filterer
{

   public function __construct()
   {
      // make sure that filter extension is installed
		if(!function_exists('filter_list')) {
			throw new Exception(__CLASS__ . ' requires filter functions in php 5.2 > or PECL');
		}
   }

   public function filter($values)
   {
      return filter_var($values, \FILTER_SANITIZE_MAGIC_QUOTES);
   }
}