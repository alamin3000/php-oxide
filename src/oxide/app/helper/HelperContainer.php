<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\base\Container;
use oxide\http\Context;

class HelperContainer extends Container  {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_helpers = [
         'util' 		=> 'oxide\app\helper\Util',         
         'html' 		=> 'oxide\app\helper\Html',
         'flash' 		=> 'oxide\app\helper\Flash', 
         'head' 		=> 'oxide\app\helper\Head', 
         'ui' 			=> 'oxide\app\helper\Ui',
         'master' 	=> 'oxide\app\helper\Master',
         'url' 		=> 'oxide\app\helper\Url',
         'locale' 	=> 'oxide\app\helper\Locale',
         'formatter' => 'oxide\app\helper\Formatter'
      ],
           
      $_context = null;
   
   
   public function __construct(Context $context) {
      parent::__construct();
      $this->_context = $context;
      $this->registerBuiltInHelpers();
   }

   /**
    * Get the application context object
    * 
    * @return Context
    */
   public function getContext() {
      return $this->_context;
   }
   
   /**
    * Register a helper
    * 
    * array must be in following format:
    * [
    *    'helpername' => 'full\path\to\Class
    * ]
    * @param array $helpers
    */
   protected function registerBuiltInHelpers() {
	   $helpers = $this->_helpers;
      foreach($helpers as $name => $helper) {
         $this->addResolver($name, function($c) use($helper) {
            return new $helper($c);
         });
      }
   }
   
   public function __invoke() {
	   $args = func_get_args();
	   if(empty($args)) {
		   return $this;
	   }
	   
	   if(count($args) > 1) {
		   $helpers = [];
		   foreach($args as $name) {
			   $helpers[] = $this->get($name);
		   }
		   
		   return $helpers;
	   } else {
		  	return $this->get($args[0]);
	   }
	   
   }
   
   public function __get($key) {
	   return $this->get($key, null, true);
   }
   
   public function __set($key, $value) {
	   if($value instanceof \Closure) {
		   $this->addResolver($key, $value);
	   } else {
		   $this->set($key, $value);
	   }
   }

}