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

class HelperContainer extends Container {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected
      $_helpers = [
         'html' 		=> '\oxide\app\helper\Html',
         'flash' 		=> '\oxide\app\helper\Flash', 
         'master' 	=> '\oxide\app\helper\Master',
         'url' 		=> '\oxide\app\helper\Url',
         'locale' 	=> '\oxide\app\helper\Locale',
         'formatter' => '\oxide\app\helper\Formatter'
      ],
           
      $_context = null;
   
   /**
    * 
    * @param Context $context
    */
   public function __construct(Context $context) {
      parent::__construct();
      $this->setFlags(self::ARRAY_AS_PROPS);
      
      $this->set('context', $context);
      $this->registerBuiltInHelpers();
   }
   
   /**
    * Register a helper with given name and class name
    * @param type $name
    * @param type $helperClass
    */
   public function registerHelper($name, $helperClass) {
      $this->addResolver($name, function(Container $c) use ($helperClass) {
         $instance = $c->instanciate($helperClass);
         return $instance;
      });
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
         $this->registerHelper($name, $helper);
      }
   }
   
   /**
    * Override getter to allow multiple get using array
    * 
    * @param type $key
    * @return type
    */
   public function offsetGet($key) {
      if(!is_array($key)) return parent::offsetGet($key);
      else {
         $vals = [];
         foreach($key as $akey) {
            $vals[] = parent::offsetGet($akey);
         }
         
         return $vals;
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