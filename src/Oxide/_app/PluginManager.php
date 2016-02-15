<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\http\Context;

/**
 * Description of PluginManager
 *
 * @author alaminahmed
 */
class PluginManager {
   
   protected 
      $_context = null;


   public function __construct(Context $context) {
      $this->_context = $context;
   }
   
   public function register($plugin, $namespace = null) {
      
   }
   
}
