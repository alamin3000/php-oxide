<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\base\Container;
use oxide\http\Context;

/**
 */
class ViewData extends Container {
	public
		$context = null;
   
   /**
    * Construct a new view data container
    * 
    * @param mixed $data
    */
   public function __construct($data = null, Context $context = null) {
      parent::__construct($data);
      if($context) $this->context = $context;
   }
   
   
   /**
    * get the application context.
    * 
    * @return Context
    */
   public function getContext() {
	   return $this->context;
   }
}