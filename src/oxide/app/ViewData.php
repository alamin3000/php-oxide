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

/**
 */
class ViewData extends Container {
   protected
      $_helpers = null;
   
   /**
    * Construct a new view data container
    * 
    * @param mixed $data
    */
   public function __construct($data = null) {
      parent::__construct($data);
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
}