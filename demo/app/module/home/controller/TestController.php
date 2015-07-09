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
use oxide\data\model;
use oxide\ui\html;

class User extends model\ActiveRecord {
   static protected 
           $_pk = 'user_pk',
           $_table = 'user',
           $_schema = null;
   
   public function __construct(array $data = null, \oxide\data\Connection $conn = null) {
      parent::__construct($data, $conn);
   }
}


class TestController extends Controller {
   
   protected function onInit(Context $context) {
      parent::onInit($context);
      
      
   }

 
   protected function executeIndex() {
      $div = new html\Element('div');
      $model = User::find(1);
      
      $model->email = 'something';
      $model['email'] = 'something else';
      
      var_dump(isset($model['name']));
      
//      var_dump($model->getModifiedKeys());
      \oxide\dump($model);
      
      return new \oxide\app\View($div);
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