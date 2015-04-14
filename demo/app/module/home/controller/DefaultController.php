<?php
namespace app\module\home\controller;
use oxide\app\Controller;
use oxide\http\Context;
use oxide\app\ViewData;
use oxide\ui\html;
use oxide\data\model;
use oxide\util\Debug;

/**
 * Description of HomeDefaultController
 *x`
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex(Context $context, ViewData $data) {
   }
   
   protected function executeSomething() {
	   $el = new \oxide\ui\html\Element('p', 'Testing default fall back action handling');
	   return new \oxide\app\View($el);
   }
   
   protected function executeEmail() {
      $this->_autoRender = false;
      
      error_reporting(-1);
    ini_set('display_errors', 'On');

    $headers = array("From: a.alamin@me.com",
    "X-Mailer: PHP/" . PHP_VERSION
    );
    $headers = implode("\r\n", $headers);
    $didhappen = mail('aahmed753@me.com', 'test', 'test', $headers);

     if($didhappen) {
        echo 'true';
     } else {
        echo 'false';
     }
   }
}