<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */
namespace oxide\http;

class Server {
   /**
    * Getting server variable
    * 
    * Using explicit checking because filter_input may not work with INPUT_SERVER in some servers
    * Code take from https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=730094
    * @param type $key
    * @param type $default
    * @return type
    */
   public static function vars($var = null, $default = null, $filter = null, $opt = null) {
      $key = ucwords($var);
      if(filter_has_var(INPUT_SERVER, $key)) {
         return filter_input(INPUT_SERVER, $key, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
      } else {
         if(isset($_SERVER[$key]))
            return filter_var($_SERVER[$key], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
         else
            return $default;
      }
   }
}