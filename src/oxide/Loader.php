<?php
namespace oxide;
use oxide\helper\_util;
use oxide\helper\_app;

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
      _app::init($appdir); //initialize the _app helper
      $config = _app::config(); 
      
      // creating the application context
      $context = new http\Context();
      http\Context::setDefaultInstance($context);
      $context->set('config', $config, true); // set the application config.
      //
      // create the front controller and set the default instance
      $fc = new http\FrontController($context);
      http\FrontController::setDefaultInstance($fc);
      
      $classnamegenerator = function($name, $namespace = null) {
         $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
         if($namespace) {
            $class = "{$namespace}\\{$class}";
         }
         
         return $class;
      };
      
      $initializer = function($name, $namespace = null, $args = null) {
         $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
         if($namespace) {
            $class = "{$namespace}\\{$class}";
         }
         $method = 'initialize';
         if(method_exists($class, $method)) {
            $class::{$method}($args);
            return true;
         } else {
            return false;
         }
      };
      
      // bootstrap
      $bootstraps = _util::value($config, 'bootstraps', null);
      if($bootstraps) {
         foreach($bootstraps as $namespace => $info) {
            if(isset($info['dir'])) {
               self::$namespaces[$namespace] = $info['dir']; // register the namespace
            }
            
            $modules = _util::value($info, 'modules', null);
            if($modules) {
               $router = $fc->getRouter();
               foreach($modules as $module => $dir) {
                  $dirtoclass = str_replace('/', '\\', $dir);                  
                  $dirtoclass = $namespace . '\\'. $dirtoclass;
                  $router->register($module, $dirtoclass);
                  
                  $initializer($module, $dirtoclass, $fc);
               }
            }
            
            $plugins = _util::value($info, 'plugins', null);
            if($plugins) {
               foreach($plugins as $plugin => $dir) {
                  $subnamespace = str_replace('/', '\\', $dir);
                  $subnamespace = $namespace . '\\'. $subnamespace;
                  
                  $pluginclass = $classnamegenerator($plugin, $subnamespace);
                  $instance = new $pluginclass();
                  if($instance instanceof plugin\Pluggable) {
                     $instance->plug($context);
                  } else {
                     throw new \Exception("Plugin ({$plugin}) is not Pluggable.");
                  }
               }
            }
         }
      }
      
		if($autorun) {
			$fc->run();
		}
		
      
      
		return $fc;
   }
}