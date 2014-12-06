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
use oxide\util\ConfigManager;
use oxide\util\EventNotifier;

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
    * 
    * @param type $file
    * @param type $dir
    * @return boolean
    */
   public static function loadFile($file, $dir = null) {
      $filename = null;
      if($dir) {
         $filename = "{$dir}/{$file}";
      } else {
         $filename = $file;
      }
      
      if(file_exists($filename)) {
         require_once $filename;
         return true;
      } else {
         return false;
      }
   }
      
   /**
    * 
    * @param type $classname
    */
   static public function loadHelper($helper, $namespace = null) {
      if(!$namespace) {
         $namespace = 'oxide';
      }
      $classname = ucfirst($helper);
      $class = "{$namespace}\\helper\\{$classname}";
      $instance = $class::getInstance();
      return $instance;
   }
   
   
   /**
    * Register autoload for the framework
    */
   static public function register_autoload() {
      $dir = dirname(__FILE__);
      self::$namespaces['oxide'] = $dir;
      self::$helpers[] = $dir . '/helper';
      spl_autoload_register(__NAMESPACE__ .'\Loader::load');
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
      self::register_autoload();
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
      
      // creating the application context
      // and setup some services
      $context = new http\Context();
      http\Context::setDefaultInstance($context);
      $notifier = EventNotifier::defaultInstance();
      $context->set('notifier', $notifier);
      $configManager = new ConfigManager($config_dir);
      $config = $configManager->getConfig();
      $context->set('configManager', $configManager);
      $context->set('config', $config); // set the application config.
      
      
      $notifier->notify(self::EVENT_BOOTSTRAP_START, null);
      
      $fc = new http\FrontController($context);
      http\FrontController::setDefaultInstance($fc);
      
      // load modules
      $modules = _util::value($config, 'modules');
      self::loadModules($modules, $fc->getRouter());
      
      $notifier->notify(self::EVENT_BOOTSTRAP_END, null);
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