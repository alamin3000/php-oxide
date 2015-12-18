<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */
namespace oxide\util;
use oxide\base\Dictionary;

class ConfigManager {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   protected 
      $_defaultConfigFilename = 'config.json',
      $_parser = null,
      $_config = null,
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
   public function getConfig() {
      if($this->_config === null) {
         $this->_config = $this->openConfigByFilename($this->_defaultConfigFilename);
      }
      
      return $this->_config;
   }
   
   /**
    * 
    * @return FileParser
    */
   public function getFileParser() {
      if($this->_parser === null) {
         $this->_parser = new FileParser();
      }
      
      return $this->_parser;
   }
   
   /**
    * Create a config object from given $filename using current config directory
    * 
    * Filename provided must be relative to the managed configuration directory
    * @param string $filename
    * @return DataFile
    * @throws \Exception
    */
   public function openConfigByFilename($filename) {
      $dir = $this->_dir;
      $file = $dir . '/' . $filename;
      if(!is_file($file)) {
         throw new \Exception('Config file not found in location: ' . $file);
      }
      
      $parser = $this->getFileParser();
      $data = $parser->parse($file);
      return new Dictionary($data);
   }
   
   /**
    * Get config object from given relative directory
    * 
    * @param string $relative_dir
    * @param string $name
    * @return DataFile
    */
   public function openConfigByDirectory($relative_dir, $name = 'config.json') {
      $dir = trim($relative_dir, '/');
      $filename = "{$dir}/{$name}";
      return $this->openConfigByFilename($filename);
   }
}