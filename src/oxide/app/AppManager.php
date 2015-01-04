<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\data\Connection;
use oxide\data\model\EAVModel;
use oxide\http\Session;


/**
 * Application Resource Manager
 */
class AppManager {
   use \oxide\base\pattern\SingletonTrait;
   
   protected 
      $_configfile = 'config.json',
      $_dir = null,
      $_config = null,
      $_conn = null,
      $_auth = null;


   /**
    * Construct the app manager using app config directory
    * 
    * Construct is private
    * @see createWithConfigDirectory
    * @param string $configDirectory
    */
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
    * Typed verison of getInstance()
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
         $this->_config = $this->openConfig($this->_configfile);
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
         $namespace = 'oxide.app.auth';
         $auth = new auth\Authenticator(new auth\SessionStorage(new Session($namespace)));
         $this->_auth = $auth;
      }
      
      return $this->_auth;
   }
   
   public function configDirectory() {
      return $this->_dir;
   }
   
   public function publicDirectory() {
      return filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
   }
   
   /**
    * Get the main upload directory for the app.
    * 
    * @param string $subdir Subdirectory to be appended to the upload directory
    * @return string
    * @throws \Exception
    */
   public function uploadDirectory($subdir = null) {
      $config = $this->getConfig();
      $appconfig = App::config('settings', null, true);
      $updir = trim(_util::value($appconfig, 'upload_dir', null, true), '/');
      if(empty($updir)) {
         throw new \Exception('Upload directory is not found.');
      }
      
      $pubdir = self::dir_public();
      $dir = "{$pubdir}/{$updir}";
      if($subdir) {
         $dir .= "/" . trim($subdir, '/');
      }
      
      return $dir;
   }
   
   
   /**
    * Application metadata
    * 
    * @param type $key
    * @param type $value
    */
   public function metadata($key = null, $value = null)  {
      static $model = null;
      if($model == null) {
         $conn = $this->getConnection();
         $table = 'application_metadata';
         $model = new EAVModel($conn, $table);
         $model->configure(array('key' => 'data_key', 'value' => 'data_value'));
         $model->load();
      }
      
      if($key) {
         if($value) {
            $model->set($key, $value);
            $model->save();
         } else {
            return $model->get($key);
         }
      } else {
         return $model;
      }
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
   
   public function __destruct() {
      
   }
}