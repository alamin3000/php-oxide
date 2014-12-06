<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;

abstract class Debug {
   public 
      $enabled = false;
   
   
   /**
    * Log a string 
    * 
    * @param string $string
    */
   public static function log($string) {
      $trace = debug_backtrace();
      if(isset($trace[1]['class'])) {
         $caller = $trace[1]['class'];
         $line = $trace[1]['line'];
      }
      else {
         $caller = $trace[0]['file'];
         $line = $trace[0]['line'];
      }
      
      
      echo "<pre>[{$caller}:{$line}] {$string}</pre>";
   }
   
   /**
    * Dumb a variable
    * 
    * @param mixed $var
    * @param bool $returnString
    */
   public static function dump($var, $returnString = false) {
      $trace = debug_backtrace();
      echo "<pre>";      
      ob_start();
      echo call_user_func_array('var_dump', func_get_args());
      echo htmlentities(ob_get_clean());
      echo "</pre>";     
      echo "<p><strong>File:</strong> {$trace[0]["file"]} <strong>Line:</strong> {$trace[0]["line"]}</p>";      
   }
}