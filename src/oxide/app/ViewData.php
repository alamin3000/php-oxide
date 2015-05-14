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
    * Get the internal helper container
    * 
    * If no container found, it will create an empty container and return
    * @return Container
    */
   public function getHelperContainer() {
      if($this->helper == null) {
         if(helper\HelperContainer::hasSharedInstance()) {
            $this->helper = helper\HelperContainer::sharedInstance();
         } else {
	         throw new \Exception('Helper Container has not been set');
         }
      }
      
      return $this->helper;
   }
   
   /**
    * Set the helper container for the view data
    * 
    * @param Container $container
    */
   public function setHelperContainer(helper\HelperContainer $container) {
      $this->helpers = $container;
   }
   
   /**
    * Set helper
    * 
    * Helper will be shared among ALL views
    * @param type $name
    * @param type $helper
    */
   public function setHelper($name, $helper) {
      $this->getHelperContainer()->set($name, $helper);
   }
   
   /**
    * Get helper by name
    * @param string $name
    * @return mixed
    */
   public function getHelper($name) {
      return $this->getHelperContainer()->get($name);
   }
   
   
   /**
    * getHelpers function.
    * 
    * @access public
    * @param array $names
    * @return void
    */
   public function getHelpers(array $names) {
	   $helpers = [];
	   $helperContainer = $this->getHelperContainer();
	   foreach($names as $name) {
		   $helpers[] = $helperContainer[$name];
	   }
	   
	   return $helpers;
   }
}