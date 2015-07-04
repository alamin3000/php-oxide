<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

function print_time($start, $end, $message) {
   echo "<pre>";
   echo "<h3>".$message."</h3>";
   echo number_format($end - $start, 3);
   echo "</pre>";
   echo "<br/>";
}

function benchmark(Closure $script, $iteration, $title) {
   $time_start = microtime(true);
   for($i=0;$i<$iteration;$i++) {
      $script();
   }
   $time_end = microtime(true);
   print_time($time_start, $time_end, $title);
}

class A {
   public $c;
   
   public function b() {
      return;
   }
   
   public function __invoke() {
      return;
   }
   
   public function __call($name, $arguments) {
      return;
   }
   
   public static function __callStatic($name, $arguments) {
      return;
   }
   
   public function __get($name) {
      return;
   }
}
$obj = new A();

$helper = new stdClass();
$helper->html = $obj;

$helper->html();


print "<h1>Benchmarks</h1>";
print "<hr/>";


//benchmark(function()  {
//   $arr = [];
//}, 1000000, "new array");
//
//
//benchmark(function()  {
//   $obj = new stdClass();
//}, 1000000, "new stdClass");
//
//benchmark(function()  {
//   $obj = new A();
//}, 1000000, "new A");

//benchmark(function() use ($obj) {
//   $obj();
//}, 1000000, "__invoke");

//benchmark(function() use ($obj) {
//   $obj->b();
//}, 1000000, "direct method");

//
//benchmark(function() use ($obj) {
//   $obj->b;
//}, 1000000, "__get");
//
//benchmark(function() use ($obj) {
//   $obj->c;
//}, 1000000, "direct property");