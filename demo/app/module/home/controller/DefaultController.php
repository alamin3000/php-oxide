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
   
   public function executeIndex_GET(Context $context) {
      
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
   
   
   protected function executeTest(Context $context) {
      $this->_autoRender = false;
      
      $options = [
          'document_root' => \oxide\helper\App::dir_public(),
          'upload_folder' => \oxide\helper\App::dir_upload()
      ];
      
      $form = new \oxide\ui\html\Form();
      $imgcontrol = new \oxide\ui\misc\ImageUrlUploadControl('imageurl', null, 'Image');
      $imgcontrol->setOptions($options);
      $form->addControl($imgcontrol);
      
      $fieldset = new \oxide\ui\html\Fieldset('fieldset1', null, 'Fieldset Info');
      $fieldset->addControl(new \oxide\ui\html\InputControl('checkbox', 'checkbox', null, 'Select'));
      $form->addFieldset($fieldset);
      
      
      $form->addControl(new \oxide\ui\html\ButtonControl('submit', 'submit', 'Submit'));
      if($form->isSubmit()) {
         $values = $form->process($result);
         \oxide\helper\Util::dump($values);
         if(!$result->isValid()) {
            print 'Not valid';
         } else {
            print 'Valid';
         }
      }
      echo $form;
   }
}