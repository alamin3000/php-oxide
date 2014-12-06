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
      $_dir = null,
      $_appConfig = null;
   
   /**
    * 
    * @param string $dir
    */
   public function __construct($dir) {
      $this->_dir =  $dir;
   }
   
   /**
    * Get the app config object
    * 
    * @return Container
    * @throws \Exception
    */
   public function getConfig() {
      if($this->_appConfig === null) {
			$this->_appConfig = $this->getConfigByFilename($this->_configFilename);
		}
      
      return $this->_appConfig;
   }
   
   /**
    * Create a config object from given $filename using current config directory
    * 
    * @param string $filename
    * @return Container
    * @throws \Exception
    */
   public function getConfigByFilename($filename) {
      $dir = $this->_dir;
      $file = $dir . '/' . $filename;
      if(!is_file($file)) {
         throw new \Exception('Application Config file not found in location: ' . $file);
      }
      $data = self::parse($file);
      return new Container($data);      
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