<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */
namespace oxide\app;
use oxide\util\DataFile;

class ConfigManager {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected 
      $_defaultConfigFilename = 'config.json',
      $_configs = [],
      $_dir = null;
   
   /**
    * Construct the config manager
    * 
    * @param string $configDir
    * @param string $defaultConfigFilename
    */
   public function __construct($configDir, $defaultConfigFilename = null) {
      $this->_dir = $configDir;
      if($defaultConfigFilename) {
         $this->_defaultConfigFilename = $defaultConfigFilename;
      }
   }
   
   /**
    * Get the app config object
    * 
    * @return Container
    * @throws \Exception
    */
   public function getConfig($filename = null) {
      if($filename == null) {
         $filename = $this->_defaultConfigFilename;
      }
      return $this->getConfigByFilename($filename);
   }
   
   /**
    * Create a config object from given $filename using current config directory
    * 
    * Filename provided must be relative to the managed configuration directory
    * @param string $filename
    * @return DataFile
    * @throws \Exception
    */
   public function getConfigByFilename($filename) {
      if(!isset($this->_configs[$filename])) {
         $dir = $this->_dir;
         $file = $dir . '/' . $filename;
         if(!is_file($file)) {
            throw new \Exception('Config file not found in location: ' . $file);
         }
         $config = new DataFile($file);
         $this->_configs[$filename] = $config;
      }
           
      return $this->_configs[$filename];
   }
   
   /**
    * Get config object from given relative directory
    * 
    * @param string $relative_dir
    * @param string $name
    * @return DataFile
    */
   public function getConfigByDirectory($relative_dir, $name = 'config.json') {
      $dir = trim($relative_dir, '/');
      $filename = "{$dir}/{$name}";
      return $this->getConfigByFilename($filename);
   }
}