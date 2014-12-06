<?php
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\helper;
use oxide\http\FrontController;
use oxide\data\Connection;
use oxide\http\Context;
use oxide\util\EventNotifier;
use oxide\data\model\EAVModel;
use oxide\util\ConfigFile;
use oxide\helper\_util;
use oxide\helper\Auth;

/**
 * Application helper class
 * 
 * Provides various application related resources such as:
 * - Application front controller instance
 * - Application context
 * - Application metadata
 * - Application database connection
 * - Application notification
 * - Application configuration 
 * - Application preferences
 * - Application directories
 */
class App {  
	protected static
      $_config_dir = null,
		$_dir = null;
		
   /**
    * Initialize the application
    * 
    * @param string $config_dir
    */
	public function init($config_dir) {
		self::$_config_dir = $config_dir;
	}

   /**
    * Get the public root directory for the current app
    * 
    * @return string
    */
   public function dir_public() {
      return filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
   }
   
   /**
    * Get the main upload directory for the app.
    * 
    * @param string $subdir Subdirectory to be appended to the upload directory
    * @return string
    * @throws \Exception
    */
   public function dir_upload($subdir = null) {
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
    * Get the user current user's upload directory
    * 
    * Basically uses dir_user_upload providing current identity.
    * User must be logged in before calling this method, else exception will be thrown
    * @return string
    * @throws \Exception
    */
   public function dir_current_user_upload() {
      $identity = Auth::identity();
      if(!$identity) {
         throw new \Exception('User is not currently logged in.');
      }
      
      return self::dir_user_upload($identity);
   }
   
   /**
    * Get the upload directory for use given the $identity
    * 
    * @param stdClass $identity
    * @return string
    */
   public function dir_user_upload($identity) {
      $u = "{$identity->username}";
      $m1 = sha1($u);
      $user_dir = "{$u}{$m1}";
      
      return self::dir_upload($user_dir);
   }
   
   /**
    * Read from application preference file.
    * 
    * If $key is not provided, complete application preferece is 
    * @staticvar type $instance
    * @param type $namespace
    * @param type $key
    * @param type $default
    * @param type $required
    * @return type
    * @throws \Exception
    */
   public function pref($namespace = null, $key = null, $default = null, $required = false) {
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
    * Application metadata
    * 
    * @param type $key
    * @param type $value
    */
   public function metadata($key = null, $value = null)  {
      static $model = null;
      if($model == null) {
         $conn = self::database();
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
}