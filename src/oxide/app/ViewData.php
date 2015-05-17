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
		$helper = null,
		$context = null;
   
   /**
    * Construct a new view data container
    * 
    * @param mixed $data
    */
   public function __construct($data = null, Context $context = null, Container $helper = null) {
      parent::__construct($data);
      if($context) $this->context = $context;
      if($helper) $this->helper = $helper;
   }
   
   /**
    * Get helper by name
    * @param string $name
    * @return mixed
    */
   public function getHelper($name) {
      return $this->helper->get($name);
   }
   
   
   /**
    * getHelpers function.
    * 
    * @access public
    * @param array $names
    * @return void
    */
   public function getHelpers() {
	   $helper = $this->helper;
	   return call_user_func_array([$this, '__invoke'], func_get_args());
   }
}