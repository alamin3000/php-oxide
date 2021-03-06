<?php
namespace app\module\home\controller;
use oxide\app\Controller;

/**
 * Description of HomeDefaultController
 *x`
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex() {
      
   }
   
   protected function executeTesting() {
      $this->autoRender = false;
      
      $helper = $this->viewData->helper;
      $helper->load(['html', 'flash', 'formatter']);
      
      $helper->flash('Error happend');
      
      
      echo $helper->tag('h1', 'Hello World');
      echo $helper->html->tag('p', 'Start something amazing.');
      echo $helper->tag('strong', $helper->flash());
      echo $helper->currency(20000);
      echo $helper->a('Hello', 'link');
   }
}