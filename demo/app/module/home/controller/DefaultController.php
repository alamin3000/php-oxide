<?php
namespace app\module\home\controller;
use oxide\mvc\Controller;
use oxide\http\Context;
use oxide\mvc\ViewData;

/**
 * Description of HomeDefaultController
 *
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex(Context $context) {
      $el = new \oxide\ui\html\Element('p', 'This is a paragraph');
      return new \oxide\mvc\View($el);
   }
   
   
   public function executeAnotherMethod(Context $context, ViewData $data) {
      print 'here';
   }
}