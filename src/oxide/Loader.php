<?php
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide;
use oxide\helper\_util;
use oxide\util\PSR4Autoloader;
use oxide\util\EventNotifier;
use oxide\http\Request;
use oxide\http\Response;

/**
 * Oxide Loader
 * 
 * Manages namespaces
 * Provides bootstrap functionality
 * 
 * In general, all Oxide framework applications should use Loader's bootstrap
 * method to start the application
 * 
 * For applications not using Composer, register_autoload() must be called before
 * making any reference to any oxide framework classes.
 * 
 * Typical application initialized and started by calling the bootstrap() method.
 */
class Loader {
   
   static protected 
      $namespaces = [],
      $helpers = [],
      $autoloader = null;
   
   const 
      EVENT_BOOTSTRAP_START = 'LoaderBootstrapStart',
      EVENT_BOOTSTRAP_END = 'LoaderBootstrapEnd';

    /**
    * Load given $classname using $dir if provided, else uses current $namespaes 
    * 
    * @param string $classname
    * @param string $dir
    * @throws \Exception
    */
   static public function load($classname, $dir = null) {
      $parts = explode('\\', $classname);
      if(!$dir) {
         if(count($parts) > 1) {
            $namespace = $parts[0]; // first part is namespace/package
            if(isset(self::$namespaces[$namespace])) {
               $dir = rtrim(self::$namespaces[$namespace], '/');
               $parts[0] = $dir; // replace the first entry with this directory
            }
         }
         $filename = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
      } else {
         $filename = $dir . DIRECTORY_SEPARATOR . end($parts) . '.php';
      }

      if(file_exists($filename)) {
         require_once $filename;
      } else {
         throw new \Exception("Unable to load class: {$classname} using "
         . "file: {$filename}", 9000);
      }
   }
   
   /**
    * Get the PSR4 Autoloader class
    * @return PSR4Autoloader
    */
   static public function getAutoloader() {
      if(self::$autoloader === null) {
         self::$autoloader = new PSR4Autoloader();
      }
      
      return self::$autoloader;
   }
   
   public static function register() {
      // registering autoload for phpoxide
      $autoloader = self::getAutoloader();
      $autoloader->register();
      $dir = dirname(__FILE__);
		$autoloader->addNamespace('oxide',$dir);
   }
   
   /**
    * Initializes the FrontController and returns the instance.
    * 
    * Performs various bootstrap processes for oxide application.
    * @param string $config_dir Configuration directory
    * @param boolean $autorun Whether or not application should start 
    * @return http\FrontController
    */
   public static function bootstrap($config_dir, $autorun = true) {
      self::register();
      
      // create the event notifier and share it
      $notifier = new EventNotifier();
      EventNotifier::setSharedInstance($notifier);      
            
      // create the config manager and get the application config
      $configManager = new app\ConfigManager($config_dir);
      app\ConfigManager::setSharedInstance($configManager);
      $config = $configManager->getConfig();
      
      $notifier->notify(self::EVENT_BOOTSTRAP_START, null);
      
      // set shared database connection manager
      // this will not connect to database.
      $connectionManager = new app\ConnectionManager($config['database']);
      app\ConnectionManager::setSharedInstance($connectionManager);
      
      // creating the http context and share it
      $request = Request::currentServerRequest();
      $context = new http\Context($request);
      http\Context::setSharedInstance($context);

      // create the front controller and share it
      $fc = new http\FrontController($context);
      http\FrontController::setSharedInstance($fc);
      
      // load modules
      $modules = _util::value($config, 'modules');
      self::loadModules($modules, $fc->getRouter());
      
      $notifier->notify(self::EVENT_BOOTSTRAP_END, null);
      
      // if autorun, run the front controller
		if($autorun) {
			$fc->run();
		}
		
		return $fc;
   }
   
   /**
    * @param array $modules
    * @param \oxide\http\Router $router
    */
   protected static function loadModules(array $modules, http\Router $router) {
      $autoloader = self::getAutoloader();
      if($modules) {
         foreach($modules as $module) {
            $name = _util::value($module, 'name', null, true);
            $dir = _util::value($module, 'dir', null, true);
            $namespace = _util::value($module, 'namespace', null, true);
            
            // register with autoloader
            $autoloader->addNamespace($namespace, $dir);
            
            // register with router
            $router->register($name, $dir, $namespace);
         }
      }
   }
   
   protected static function loadPlugins(array $plugins) {
      $plugin = function($namespace, $required = false) use ($context) {
         $class = 'Plugin';
         $fullclass = "{$namespace}\{$class}";
         
         if(class_exists($fullclass)) {
            $instance = new $fullclass();
            if($instance instanceof application\Pluggable) {
               $instance->plug($context);
            } else {
               throw new \Exception("Class ({$fullclass}) is not Pluggable.");
            }
         } else {
            if($required)
               throw new \Exception("Plugin class not found at {$namespace}.");
         }
      };
      
      $plugins = _util::value($info, 'plugins', null);
      if($plugins) {
         foreach($plugins as $plugin => $dir) {
            $subnamespace = str_replace('/', '\\', $dir);
            $subnamespace = $namespace . '\\'. $subnamespace;

            $plugin($subnamespace, true);
         }
      }
   }
}