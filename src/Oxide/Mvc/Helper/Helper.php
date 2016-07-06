<?php
namespace oxide\app\helper;
use oxide\base\Container;
use oxide\http\Context;
use oxide\base\pattern\ExtendableTrait;
use oxide\base\pattern\SharedInstanceTrait;
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

class Helper extends Container {
   use ExtendableTrait, SharedInstanceTrait;
   
   protected
      $_helpers = [
         'html' 		=> '\Oxide\app\helper\Html',
         'flash' 		=> '\oxide\app\helper\Flash', 
         'master' 	=> '\oxide\app\helper\Master',
         'url' 		=> '\oxide\app\helper\Url',
         'locale' 	=> '\oxide\app\helper\Locale',
         'formatter' => '\oxide\app\helper\Formatter'
      ];
   
   /**
    * 
    * @param Context $context
    */
   public function __construct(Context $context) {
      parent::__construct(['context' => $context]);
      $this->setFlags(self::ARRAY_AS_PROPS);
      $this->selfResolvingName = 'helper';
      
      foreach($this->_helpers as $helper => $class) {
         $this->register($helper, $class);
      }
   }
   
   
   
   /**
    * Register a helper with given $class
    * 
    * Simply adds a resolver with the $calss name
    * $class needs to be fully qualified class name, including namespace
    * @param string $name
    * @param string $class
    */
   public function register($name, $class) {
      $this->bind($name, function(Helper $helper) use ($name, $class) {
         $instance = $helper->instanciate($class);
         $helper->{$name} = $instance;
         $helper->extendObject($instance);
         return $instance;
      });
   }
   
   /**
    * Loads helper and return instance
    * 
    * @param string $name
    * @param string $class
    * @return 
    */
   public function load($name, $class = null) {
      if($class) {
         $this->register($name, $class);
      }
      return $this->get($name);
   }
   
   /**
    * Load the helper on fly.  
    * 
    * Once loaded, this will not be called, 
    * since a public property will be registered
    * @param type $name
    * @return type
    */
   public function __get($name) {
      return $this->load($name);
   }
      
   
   /**
    * 
    * @param type $name
    * @param type $arguments
    * @return type
    */
   public function __call($name, $arguments) {
      return $this->invokeExtended($name, $arguments);
   }
}