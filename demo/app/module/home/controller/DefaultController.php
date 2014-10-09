<?php
namespace home\controller;
use oxide\application\ActionController;
use oxide\http\Context;


/**
 * Description of HomeDefaultController
 *
 * @copyright Alifsoft LLC
 */
class DefaultController extends ActionController
{
   /**
    * initialize the controller
    * 
    * shared with all action methods in this controller
    * @param Context $context 
    */
   protected  function onInit(Context $context)
   {
      parent::onInit($context);
   }
   
   public function executeIndex(Context $context) {
   }
   
   /**
    * handle all controller level exceptions
    * 
    * @param Context $context 
    */
   protected function onException(Context $context, \Exception $exeption) 
   {
      parent::onException($context, $exeption);
   }

   /**
    * clean up
    * @param Context $context 
    */
   protected function onExit(Context $context) 
   {
      parent::onExit($context);
   }
}