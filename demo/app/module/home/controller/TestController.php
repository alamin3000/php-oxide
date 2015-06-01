<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace app\module\home\controller;
use oxide\app\Controller;
use oxide\http\Context;
use oxide\app\ViewData;
use oxide\ui\html;
use oxide\app\View;


class TestController extends Controller {


   public function onInit(Context $context) {
      parent::onInit($context);
   }
   
   
   public function executeIndex(Context $context, ViewData $data) {
      
   }
   
   public function executeCore(Context $context) {
      
   }
   
   
   
   public function executeUiControl(Context $context, ViewData $data) {
      $section = new html\Element('section');
      $section[] = new html\Element('h1', 'Form Control testing');

      // creating form
      $form = new html\Form();
      $textInput = new html\TextControl('text', null, 'Text');
      $textInput->setInfo('Any string.');
      $textInput->setError('Some error happend.');
      $form[] = $textInput;
      
      $fieldset = new html\Fieldset('fieldset', 'My Info');
      $sex = new html\CheckboxGroupControl('sex', 'female', 'Your sex', ['male' => 'Male', 'female' => 'Female', 'unknown' => 'Unknown']);
      $fieldset[] = $sex;
      $form[] = $fieldset;
      
      
      
      
      $section[] = $form;      
      return new View($section);
   }
   
   /**
    * Test ui package's Tag and Elements
    * 
    * @param Context $context
    * @param ViewData $data
    */
   public function executeUiElement(Context $context, ViewData $data) {
      $this->_autoRender = false;
      
      $section = new html\Element('section');
      $section[] = new html\Element('h1', 'Hellow World');
      $hr = new html\Tag('hr', null, true);
      $hr->setWrapperTag(new html\Tag('p'));
      $section[] = $hr;
      
      echo $section->render();
   }
   
   
   protected function onRender(Context $context, View $view = null) {
      parent::onRender($context, $view);
   }
   
   protected function onException(Context $context, \Exception $e) {
      parent::onException($context, $e);
   }
   
   protected function onExit(Context $context) {
      parent::onExit($context);
   }
}