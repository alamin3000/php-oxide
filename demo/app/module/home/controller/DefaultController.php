<?php
namespace app\module\home\controller;
use oxide\app\Controller;
use oxide\http\Context;
use oxide\app\ViewData;
use oxide\app\helper;
use oxide\util\Debug;
use oxide\ui\html;
use oxide\app\View;


/**
 * Description of HomeDefaultController
 *x`
 * @copyright Alifsoft LLC
 */
class DefaultController extends Controller {
   protected function executeIndex(Context $context, ViewData $data) {
      $ui = $data->getHelper('ui');

      $section = new html\Element('section');
      $h1 = new html\Element('h1', 'Hello World');
      $section[] = $h1;

      $textInput = new html\InputControl('text', 'username', null, 'Username');
      $textInput->setInfo('Choose a nickname');
      
      $form = new html\Form();
      $form[] = $textInput;
     
      $form->append(new html\HiddenControl('hidden'));
      $form->append(new html\SubmitControl('submit', 'Submit'));

      $section[] = $form;
      // $section[] = $input;
      return new View($section);
   }
}