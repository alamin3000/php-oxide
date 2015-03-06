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
 *
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex(Context $context, ViewData $data) {
      $conn = $context->getConnection();
      
      $gateway = new model\TableGateway($conn, 'user', 'user_pk');
		$columns = $gateway->discoverSchema();
		Debug::dump($columns);
   }
   
   protected function executeSomething() {
	   $el = new \oxide\ui\html\Element('p', 'Testing default fall back action handling');
	   return new \oxide\app\View($el);
   }
}