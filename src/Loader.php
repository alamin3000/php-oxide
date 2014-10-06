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
    * register autoload for the frame work
    */
   static public function register_autoload() {
      self::$namespaces['oxide'] = dirname(__FILE__);
		self::$namespaces['Zend'] = dirname(__FILE__) . '/../vendor/Zend';
      spl_autoload_register(__NAMESPACE__ .'\Loader::load');
   }
   
   /**
    * Initializes the FrontController and returns the instance.
    * @param array $config
    * @return \oxide\http\FrontController
    */
   public static function bootstrap($appdir, $autostart = true) {
      App::init($appdir);
      $config = App::config();
      $context = App::context();
      $fc = App::instance();
		$context->set('config', $config, true); // set the application config
		
      $bootstraps = Util::value($config, 'bootstrap', null);
      if($bootstraps) {
         foreach($bootstraps as $namespace => $dir) {
            self::$namespaces[$namespace] = $dir;
            $class = ucfirst($namespace);
            $fullclass = "{$namespace}\\{$class}";
            $method = "initialize";
            if(method_exists($fullclass, $method)) {
               $fullclass::{$method}($context);
            }
         }
      }
      
		if($autostart) {
			$fc->execute($context);
		}
		
		return $fc;
   }
}