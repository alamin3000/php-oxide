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
use oxide\data\Connection;

class AppManager {
   use \oxide\base\pattern\SingletonTrait;
   
   protected 
      $_configfile = 'config.json',
      $_dir = null,
      $_config = null,
      $_conn = null,
      $_auth = null;


   private function __construct($configDirectory) {
      $this->_dir = $configDirectory;
   }
   
   /**
    * Instanciate the app manager using config directory
    * 
    * @param type $dir
    * @return self
    * @throws \Exception
    */
   public static function createWithConfigDirectory($dir) {
      if(self::$_t_instance !== null) {
         throw new \Exception('App manager already has been created.');
      }
      
      $instance = new self($dir);
      self::$_t_instance = $instance;
      return $instance;
   }
   
   /**
    * 
    * @return self
    */
   public static function instance() {
      return self::getInstance();
   }

   /**
    * Gets the main application config
    * @return DataFile
    */
   public function getConfig() {
      if($this->_config === null) {
         $this->_config = $this->_configfile;
      }
            
      return $this->_config;
   }
   
   /**
    * Open a configuration file relative from the application config directory
    * @param string $filename
    * @return DataFile
    * @throws \Exception
    */
   public function openConfig($filename) {
      $dir = $this->_dir;
      $file = $dir . '/' . $filename;
      if(!is_file($file)) {
         throw new \Exception('Config file not found in location: ' . $file);
      }

      $config = new DataFile($file);
      $config->load();
      
      return $config;
   }
   
   /**
    * Gets the main application connection
    * 
    * @return type
    * @throws \Exception
    */
   public function getConnection() {
      if($this->_conn === null) {
         $config = $this->getConfig();
         $dbConfig = $config['database'];
         if(!$dbConfig) {
            throw new \Exception('Database connection settings not found in config.');
         }
         
         $dbOptions = [
               \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
               'FETCH_MODE'			=> \PDO::FETCH_ASSOC];
         $conn = new Connection($dbConfig, $dbOptions);
         $this->_conn = $conn;
      }
      
      return $this->_conn;
   }
   
   /**
    * 
    * @return auth\Authenticator
    */
   public function getAuth() {
      if($this->_auth === null) {
         $auth = new auth\Authenticator(new auth\SessionStorage());
         $this->_auth = $auth;
      }
      
      return $this->_auth;
   }
   
   
   /**
    * 
    * @return DataFile
    */
   public static function mainConfig() {
      return self::getInstance()->getConfig();
   }
   
   /**
    * 
    * @return Connection
    */
   public static function sharedConnection() {
      return self::getInstance()->getConnection();
   }
   
   /**
    * 
    * @return type
    */
   public static function currentIdentity() {
      return self::getInstance()->getAuth()->getIdentity();
   }
}