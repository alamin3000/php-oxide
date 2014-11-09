<?php
namespace oxide\helper;
use oxide\http\FrontController;
use oxide\data\Connection;
use oxide\http\Context;
use oxide\util\EventNotifier;
use oxide\data\model\DbKeyValueObject;
use oxide\util\ConfigFile;
use oxide\helper\_util;
use oxide\helper\Auth;

abstract class App {  
	protected static
      $_config_dir = null,
		$_dir = null;
		
   /**
    * Initialize the application
    * @param type $config_dir
    */
	public static function init($config_dir) {
		self::$_config_dir = $config_dir;
	}
	
   /**
    * Get application's directory
    * @param string $name
    * @return string
    */
	public static function dir($name = null) {
      throw new \Exception('not implemented yet.');
		$dir = self::$_dir;
		if($name) {
			return $dir . '/' . $name;
		}
		return $dir;
	}
   
   /**
    * Get the public root directory for the current app
    * @return string
    */
   public static function dir_public() {
      return filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
   }
   
   /**
    * Get the main upload directory for the app
    * @param string $subdir Subdirectory to be appended to the upload directory
    * @return type
    * @throws \Exception
    */
   public static function dir_upload($subdir = null) {
      $appconfig = App::config('app', null, true);
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
    * Get the user current user's upload directory
    * Basically uses dir_user_upload providing current identity.
    * User must be logged in before calling this method, else exception will be thrown
    * @return string
    * @throws \Exception
    */
   public static function dir_current_user_upload() {
      $identity = Auth::identity();
      if(!$identity) {
         throw new \Exception('User is not currently logged in.');
      }
      
      return self::dir_user_upload($identity);
   }
   
   /**
    * Get the upload directory for use given the $identity
    * @param stdClass $identity
    * @return string
    */
   public static function dir_user_upload($identity) {
      $u = "{$identity->username}";
      $m1 = sha1($u);
      $user_dir = "{$u}{$m1}";
      
      return self::dir_upload($user_dir);
   }
    		
	/**
    * @staticvar type $config
    * @param type $key
    * @param type $default
    * @param type $throwerror
    * @return 
    * @throws \Exception
    */
	public static function config($key = null, $default = null, $throwerror = false) {
		static $config = null;
		if($config === null) {
			$dir = self::$_config_dir;
			$file = $dir . '/config.json';
			if(!is_file($file)) {
				throw new \Exception('Application Config file not found in location: ' . $file);
			}
			
			$config = new ConfigFile($file);
		}
		
		return _util::value($config, $key, $default, $throwerror);
	}
   
   /**
    * 
    * @staticvar type $instance
    * @param type $namespace
    * @param type $key
    * @param type $default
    * @param type $required
    * @return type
    * @throws \Exception
    */
   public static function pref($namespace = null, $key = null, $default = null, $required = false) {
      static $instance = null;
      if($instance === null) {
         $dir = self::$_config_dir;
         $file = $dir . '/pref.json';
         if(!is_file($file)) {
            throw new \Exception('Application preference file is not found in location: '. $file);
         }
         
         $instance = new ConfigFile($file);
      }
      
      if($namespace === null) return $instance;
      
      if(isset($instance[$namespace])) {
         $prefs = $instance[$namespace];
         return _util::value($prefs, $key, $default, $required);
      } else {
         if($required) {
            throw new \Exception("No preferences found for namespace: $namespace");
         } else {
            return $default;
         }
      }
   }
	
	/**
    * Returns default front controller instance
    * @return FrontController
    */
	public static function instance() {
      if(!FrontController::hasDefaultInstance()) {
         throw new \Exception("Deafult Front Controller instance not found.");
      }
		return FrontController::defaultInstance();
	}

   /**
    * Get the main/default application context object
    * @return Context
    */
   public static function context() {
      if(!Context::hasDefaultInstance()) {
         throw new \Exception("Deafult Context instance not found.");
      }
      return Context::defaultInstance();
   }
   
   /**
    * Get default application notifier
    * @return EventNotifier
    */
   public static function notifier() {
      return EventNotifier::defaultInstance();
   }
   
   /**
    * Returns main/default application's database connection
    * @return Connection
    */
   public static function database() {
	   if(!Connection::hasDefaultInstance()) {
		   $config = self::config();
		   $dbconfig = (array) $config['database'];
		  	$dboptions = array(
					\PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
					'FETCH_MODE'			=> \PDO::FETCH_ASSOC
					);
			$conn = new Connection($dbconfig, $dboptions);
			Connection::setDefaultInstance($conn);
		}
	
		return Connection::defaultInstance();
   }
      
   /**
    * Application metadata
    * 
    * @param type $key
    * @param type $value
    */
   public static function metadata($key = null, $value = null)  {
      static $model = null;
      if($model == null) {
         $conn = self::database();
         $table = 'application_metadata';
         $model = new DbKeyValueObject($conn, $table);
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
}