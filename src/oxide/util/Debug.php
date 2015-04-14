<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;
use oxide\http\Response;

abstract class Debug {
   public 
      $enabled = false;
   
   static protected
   	$_response = null;
   	
   const
      ERR_CRITICAL = 90000,
      ERR_NOTIFY = 90001,
      ERR_UNKNOWN = 99999;
   
   
   /**
    * breakpoint function.
    * 
    * @access public
    * @static
    * @return void
    */
   public static function breakpoint() {
	   $trace = debug_backtrace();
	   if(isset($trace[1]['class'])) {
         $caller = $trace[1]['class'];
         $line = $trace[1]['line'];
         $file = $trace[1]['file'];
      }
      else {
         $caller = $trace[0]['class'];
         $line = $trace[0]['line'];
         $file = $trace[0]['file'];
      }
      
      return ['caller' => $caller, 'file' => $file, 'line' => $line];
   }
   
   /**
    * setResponse function.
    * 
    * @access public
    * @static
    * @param Response $response
    * @return void
    */
   public static function setResponse(Response $response) {
	   self::$_response = $response;
   }
   
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
    * console function.
    * 
    * @access public
    * @static
    * @param mixed $var
    * @return void
    */
   public static function console($var) {
	   $breakpoint = self::breakpoint();
	   $breakpoint['var'] = $var;
		$str = "<script>console.log('PHP: ".json_encode($var)."');</script>";
		
		if(self::$_response) {
			self::$_response->addBody($str);   
	   } else {
		   echo $str;
	   }
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