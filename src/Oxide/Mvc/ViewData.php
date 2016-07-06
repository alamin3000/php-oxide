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
use oxide\app\helper\Helper;

/**
 */
class ViewData extends Container {
   public
      $title = null,
           
      /**
       * @var HelperContainer
       */
      $helper = null;
   
   
   /**
    * Construct a new view data container
    * 
    * @param mixed $data
    */
   public function __construct($data = null, Helper $helper = null) {
      parent::__construct($data);
      if($helper) $this->helper = $helper;
      else $this->helper = Helper::sharedInstance ();
   }
   
   /**
    * getHelpers function.
    * 
    * @access public
    * @param array $names
    * @return void
    */
   public function getHelper(...$helpers) {
	   $helper = $this->helper;
	   return $helper->get(...$helpers);
   }
}