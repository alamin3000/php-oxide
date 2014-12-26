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
use oxide\base\pattern\DefaultInstanceTrait;

class ConfigManager {
   use DefaultInstanceTrait;
   
   protected 
      $_configFilename = 'config.json',
      $_configs = [],
      $_dir = null;
   
   /**
    * 
    * @param string $dir
    */
   public function __construct($dir, $filename = null) {
      $this->_dir =  $dir;
      if($filename) $this->_configFilename = $filename;
   }
   
   /**
    * Get the app config object
    * 
    * @return Container
    * @throws \Exception
    */
   public function getConfig() {
      return $this->getConfigByFilename($this->_configFilename);
   }
   
   /**
    * Create a config object from given $filename using current config directory
    * 
    * @param string $filename
    * @return Container
    * @throws \Exception
    */
   public function getConfigByFilename($filename) {
      if(!isset($this->_configs[$filename])) {
         $dir = $this->_dir;
         $file = $dir . '/' . $filename;
         if(!is_file($file)) {
            throw new \Exception('Application Config file not found in location: ' . $file);
         }
         $data = self::parse($file);
         $config = new Container($data); 
         $this->_configs[$filename] = $config;
      }
           
      return $this->_configs[$filename];
   }
   
   public function getConfigByDirectory($relative_dir, $name = 'config.json') {
      $dir = trim($relative_dir, '/');
      $filename = "{$dir}/{$name}";
      return $this->getConfigByFilename($filename);
   }
   
   
   /**
    * Parse the given file into an array
    * 
    * @param type $file
    * @return array
    * @throws \Exception
    */
   public static function parse($file) {
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