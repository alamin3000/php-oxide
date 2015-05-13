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
    * Get the internal helper container
    * 
    * If no container found, it will create an empty container and return
    * @return Container
    */
   public function getHelperContainer() {
      if($this->_helpers == null) {
         if(helper\HelperContainer::hasSharedInstance()) {
            $this->_helpers = helper\HelperContainer::sharedInstance();
         } else {
	         throw new \Exception('Helper Container has not been set');
         }
      }
      
      return $this->_helpers;
   }
   
   /**
    * Set the helper container for the view data
    * 
    * @param Container $container
    */
   public function setHelperContainer(helper\HelperContainer $container) {
      $this->_helpers = $container;
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