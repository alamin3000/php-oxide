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
use oxide\app\helper\HelperContainer;

/**
 */
class ViewData extends Container {	
   public
      $title = null,
           
       
           
      /**
       * @var HelperContainer
       */
      $helpers = null;
   
   
   /**
    * Construct a new view data container
    * 
    * @param mixed $data
    */
   public function __construct($data = null, HelperContainer $helpers = null) {
      parent::__construct($data);
      if($helpers) $this->helpers = $helpers;
   }
   
   /**
    * getHelpers function.
    * 
    * @access public
    * @param array $names
    * @return void
    */
   public function getHelpers() {
	   $helpers = $this->helpers;
	   return $helpers->get(func_get_args());
   }
}