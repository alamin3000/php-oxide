<?php
namespace app\module\home\controller;
use oxide\app\Controller;
use oxide\app;

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
      
   }
}