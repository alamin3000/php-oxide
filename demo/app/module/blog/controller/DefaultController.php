<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace app\module\blog\controller;
use oxide\app\Controller;
use oxide\http\Context;


class DefaultController extends Controller {
   
   protected function onExecute(Context $context) {
//      parent::onExecute($context);
      \oxide\dump($this->_route);
      \oxide\dump($this->getParams());
   }
}
