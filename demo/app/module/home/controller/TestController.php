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
use oxide\data\model\ActiveRecord;
use oxide\ui\html;

class User extends ActiveRecord {
   static protected 
           $_pk = 'user_pk',
           $_table = 'user';
   
   public function __construct(array $data = null, \oxide\data\Connection $conn = null) {
      parent::__construct($data, $conn);
   }
}


class TestController extends Controller {
   
   protected function onInit(Context $context) {
      parent::onInit($context);
      
      
   }

 
   protected function executeIndex() {
      $form = new html\Form('myform');
      $form[] = new html\TextControl('name', null, 'Full Name');
      $form[] = new html\EmailControl('email', null, 'Email');
      
      $fieldset = new html\Fieldset('myfields', 'Basic info');
      $fieldset[] = new html\PasswordControl('password', null, 'Password');
      $fieldset[] = new html\PasswordControl('repassword', null, 'Retype password');
      $form[] = $fieldset;
      
      $group = new html\Fieldset('sexgroup', 'Personal');
      $group[] = new html\RadioGroupControl('sex', null, 'Your gender', ['Male' => 'male', 'Female' => 'female']);
      $form[] = $group;
      
      \oxide\dump($form);
      return new \oxide\app\View($form);
   }
   
   protected function executeFilter() {
      $filter = new \oxide\validation\InputFilter(FILTER_VALIDATE_EMAIL);
      $filtered = $filter->filter('abcx*9(');
      $val = $filter->validate('abcx*9(');
      
      var_dump($filtered);
      var_dump($val);
      
      return new \oxide\app\View();
   }


   protected function onUndefinedAction($action, array $params) {
      array_unshift($params, $action);
      $this->forward('index', $params);
   }
}