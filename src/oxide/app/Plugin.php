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

abstract class Plugin implements Pluggable {
   
   protected 
      /**
       * @var helper/HelperContainer 
       */
      $helpers = null,
           
      /**
       * @var Context
       */
      $context = null;


   final public function __construct(Context $context) {
      $this->context = $context;
   }
}