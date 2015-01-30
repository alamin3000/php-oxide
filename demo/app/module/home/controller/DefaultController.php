<?php
namespace app\module\home\controller;
use oxide\app\Controller;
use oxide\http\Context;
use oxide\app\ViewData;
use oxide\ui\html;

/**
 * Description of HomeDefaultController
 *
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex(Context $context, \oxide\app\ViewData $data) {
      $ui = $data->getHelper('ui');
      $div = new html\Element('div');
      $div->append(new html\Tag('hr', null, true));
      
      $form = new html\Form('myform', 'post');
      
      // text control
      $text = new html\TextControl('username', null, 'Username');
      $text->setError('invalid');
      $text->setInfo('You can use your email address.');
      $form[] = $text;
      
      // password control
      $password = new html\PasswordControl('password', null, 'Password');
      $form[] = $password;
      
      // select control
      $redirect = new html\SelectControl('redirect', null, 'Go to');
      $redirect->setData([
         'My account' => '/path/to/myaccount',
         'Dashboard' => '/path/to/dashboard'
      ]);
      $redirect->setAttribute('size', 3);
      $redirect->allowMultiple(true);
      $form[] = $redirect;
      
      // radio control
      $log = new html\RadioGroupControl('log', 'yes', 'Should log');
      $log->setData([
         'Yes' => 'yes',
         'No' => 'no'
      ]);
      $form[] = $log;
      
      
      // checkbox
      $agree = new html\CheckboxControl('license', 'agree', 'Do you agree?');
      $form[] = $agree;
      
      // multi checkbox
      $colors = new html\CheckboxGroupControl('colors', null, 'Select Colors');
      $colors->setData([
         'Red' => 'red',
         'Blue' => 'blue',
         'Yellow' => 'yellow'
      ]);
      $form[] = $colors;
      
      // submit button
      $submit = new html\SubmitControl('submit', 'Submit');
      $form[] = $submit;
      
//      $div[] = $ui->renderForm($form);
      $div[] = $form;
      
      if($form->isSubmit()) {
         print 'yes posted';
      }
      
      $view = new \oxide\app\View($div);
      return $view;
   }
   
   
   public function executeTest(Context $context, ViewData $data) {
      if (preg_match('^(?:[a-z]+:)?$', 'http://hell')){
         echo 'valid';
      } else {
         echo 'not valid';
      }
        
        
        
      die();
   }
   
   public function executeUi(Context $context, ViewData $data) {
   
   }
}