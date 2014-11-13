<?php
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide;
use oxide\helper\Util;
use oxide\helper\App;
use oxide\helper\Notifier;

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
   static public 
      /**
       * @var array Application namespaces
       */
      $namespaces = [];
   
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
    * Register autoload for the framework
    */
   static public function register_autoload() {
      self::$namespaces['oxide'] = dirname(__FILE__);
      spl_autoload_register(__NAMESPACE__ .'\Loader::load');
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
      // registering autoload for phpoxide
      self::register_autoload();
      App::init($config_dir); //initialize the App helper
      $config = App::config(); 
      
      Notifier::notify(self::EVENT_BOOTSTRAP_START, null);
      
      // creating the application context
      $context = new http\Context();
      http\Context::setDefaultInstance($context);
      $context->set('config', $config, true); // set the application config.
      //
      // create the front controller and set the default instance
      $fc = new http\FrontController($context);
      http\FrontController::setDefaultInstance($fc);
      
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
      
      // bootstrap
      $bootstraps = Util::value($config, 'bootstraps', null);
      if($bootstraps) {
         foreach($bootstraps as $namespace => $info) {
            if(isset($info['dir'])) {
               self::$namespaces[$namespace] = $info['dir']; // register the namespace
            }
                        
            $modules = Util::value($info, 'modules', null);
            if($modules) {
               $router = $fc->getRouter();
               foreach($modules as $module => $dir) {
                  $dirtoclass = str_replace('/', '\\', $dir);                  
                  $dirtoclass = $namespace . '\\'. $dirtoclass;
                  $router->register($module, $dirtoclass);
                  
                  $plugin($dirtoclass);
               }
            }
            
            $plugins = Util::value($info, 'plugins', null);
            if($plugins) {
               foreach($plugins as $plugin => $dir) {
                  $subnamespace = str_replace('/', '\\', $dir);
                  $subnamespace = $namespace . '\\'. $subnamespace;
                  
                  $plugin($subnamespace, true);
               }
            }
         }
      }
      
      Notifier::notify(self::EVENT_BOOTSTRAP_END, null);
		if($autorun) {
			$fc->run();
		}
		
		return $fc;
   }
}