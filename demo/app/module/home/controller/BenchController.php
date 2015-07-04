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
use oxide\util\Benchmarker;


class Test1 {
   public function call1() {
      
   }
   
   public function __invoke() {
      ;
   }
   
   public function __call($name, $arguments) {
      ;
   }
   
   public static function __callStatic($name, $arguments) {
      ;
   }
}


class BenchController extends Controller {
   protected
      /**
       * @var Benchmarker
       */
      $benchmarker = null;
   
   protected function onInit(Context $context) {
      parent::onInit($context);
      
      
      $this->benchmarker = new Benchmarker();
   }
   
   protected function executeIndex() {
//      $this->autoRender = false;
      
      $viewData = $this->viewData;
      $benchmarker = $this->benchmarker;
      $iteration = 1000000;
      
      $data = new \oxide\base\Dictionary();
      $data['test1'] = 'value1';
      
      $arr = new \ArrayObject();
      $arr['test2'] = 'value 2';
      $arr->test = 'value';
      
      $array = [];
      $array['test3'] = 'value 3';
      
      $b = new \oxide\ui\ArrayString();
      $b['test4'] = 'value 4';
      
      
     
      
      $viewData["Dictionary get"] = $benchmarker->benchmark(function() use ($data) {
         $data['test1'];
      }, $iteration);
      
      $viewData["ArrayObject get"] = $benchmarker->benchmark(function() use ($arr) {
         $arr['test2'];
//         return $arr->test;
      }, $iteration);
      
      $viewData["Array get"] = $benchmarker->benchmark(function() use ($array) {
         $array['test3'];
      }, $iteration);
      
      $viewData["ArrayString get"] = $benchmarker->benchmark(function() use ($b) {
         $b['test4'];
      }, $iteration);
   }
   
   protected function executeMisc() {
      $benchmarker = $this->benchmarker;
      $viewData = $this->viewData;
      $iteration = 100000;
      
      $arr = array_fill(0, $iteration, 'value');
      
      $viewData["foreach"] = $benchmarker->benchmark(function() use (&$arr) {
         $buffer = '';
         foreach($arr as $val) {
            $buffer.= $val;
         }
         
      }, 1);
      
      $viewData["implode"] = $benchmarker->benchmark(function() use (&$arr) {
         $buffer = implode('', $arr);
         
      }, 1);
      
      return $this->getViewManager()->createView('index', $viewData);
   }




   protected function executeCall() {
      $benchmarker = $this->benchmarker;
      $viewData = $this->viewData;
      $iteration = 100000;
      
      $obj = new Test1();
      
      $viewData["Direct call"] = $benchmarker->benchmark(function() use ($obj) {
         $obj->call1();
      }, $iteration);
      
      $viewData["__invoke"] = $benchmarker->benchmark(function() use ($obj) {
         $obj();
      }, $iteration);
      
      $viewData["__call"] = $benchmarker->benchmark(function() use ($obj) {
         $obj->call2();
      }, $iteration);
      
      $viewData["__callStatic using object"] = $benchmarker->benchmark(function() use ($obj) {
         $obj::call3();
      }, $iteration);
      
      $viewData["__callStatic using class"] = $benchmarker->benchmark(function() use ($obj) {
         Test1::call3();
      }, $iteration);
      
      
      return $this->getViewManager()->createView('index', $viewData);
   }
   
}