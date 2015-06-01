<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace app\module\home\controller;

class AllController extends \oxide\app\Controller {
   
   
   protected function onExecute(\oxide\http\Context $context, \oxide\app\ViewData $data) {
//      $this->_autoRender = false;
      $request = $context->getRequest();
      
      
      echo "Hello";
      return $this->getViewManager()->createView($data);
   }
}