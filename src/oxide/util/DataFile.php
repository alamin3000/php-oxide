<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;
use oxide\base\Container;

class DataFile extends Container {
   
   protected 
      $_filename = null;


   public function __construct($file) {
      parent::__construct();
      $this->_filename = $file;
   }
   
   public function load() {
      $this->setArray(self::parseFile($this->_filename));
   }
   
     /**
    * Parse the given file into an array
    * 
    * @param type $file
    * @return array
    * @throws \Exception
    */
   public static function parseFile($file) {
      // first we need to check if file exits
      if(!is_file($file)) {
         throw new \Exception("File: $file is not found.");
      }
      
      $info = pathinfo($file);
      $data = null;
      switch(strtolower($info['extension'])) {
         case 'json':
            $raw = file_get_contents($file);
            $data =json_decode($raw, true);
            
            break;
         
         case 'ini':
            $data = parse_ini_file($file, true);
            
            break;
            
         case 'php':
         	$data = include $file;
      }
      
      return $data;
   }
}