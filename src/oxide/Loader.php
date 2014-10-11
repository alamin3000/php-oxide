<?php
namespace oxide;
use oxide\helper\Util;
use oxide\helper\App;

/**
* 
*/
class Loader {
   /**
    * @var array stores namespaces and their directories
    */
   static public $namespaces = [];
   
   /**
    * Load given $classname using $dir if provided, else static::$namespaces 
    * @param type $classname
    * @param type $dir
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
         throw new \Exception("Unable to load class: {$classname} using file: {$filename}", 9000);
      }
   }
   
   /**
    * register autoload for the framework
    */
   static public function register_autoload() {
      self::$namespaces['oxide'] = dirname(__FILE__);
      spl_autoload_register(__NAMESPACE__ .'\Loader::load');
   }
   
   /**
    * Initializes the FrontController and returns the instance.
    * @param array $config
    * @return http\FrontController
    */
   public static function bootstrap($appdir, $autorun = true) {
      // registering autoload for phpoxide
      self::register_autoload();
      App::init($appdir); //initialize the App helper
      $config = App::config(); 
      
      // creating the application context
      $context = new http\Context();
      http\Context::setDefaultInstance($context);
      $context->set('config', $config, true); // set the application config.
      //
      // create the front controller and set the default instance
      $fc = new http\FrontController($context);
      http\FrontController::setDefaultInstance($fc);
      
      // bootstrap
      $bootstraps = Util::value($config, 'bootstraps', null);
      if($bootstraps) {
         foreach($bootstraps as $namespace => $dir) {
            self::$namespaces[$namespace] = $dir; // register the namespace
            $class = ucfirst($namespace);
            $fullclass = "{$namespace}\\{$class}";
            $method = "initialize";
            if(method_exists($fullclass, $method)) {
               $fullclass::{$method}($fc);
            }
         }
      }
      
      $modules = Util::value($config, 'modules', null);
      $router = $fc->getRouter();
      if($modules) {
         foreach($modules as $module => $dir) {
            self::$namespaces[$module] = $dir;
            $router->register($module, $module);
         }
      }
		if($autorun) {
			$fc->run();
		}
		
		return $fc;
   }
}