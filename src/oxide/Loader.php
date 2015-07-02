<?php
/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide;
use oxide\util\PSR4Autoloader;

/**
 * 
 * @param type $var
 */
function dump($var) {
   util\Debug::dump($var, false, 1);
}

/**
 * Function throws exception.
 * 
 * Useful when can not use throw syntax (such as ternary operation)
 * @param type $str
 * @param type $code
 * @throws \Exception
 */
function exception($str = null, $code = null) {
   throw new \Exception($str, $code);
}


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
   use base\pattern\SingletonTrait;
   
   protected 
      $autoloader = null;
   
   const 
      EVENT_BOOTSTRAP_START = 'LoaderBootstrapStart',
      EVENT_BOOTSTRAP_END = 'LoaderBootstrapEnd';
   
   /**
    * Get the PSR4 Autoloader class
    * @return PSR4Autoloader
    */
   public function getAutoloader() {
      if($this->autoloader === null) {
         $this->autoloader = new PSR4Autoloader();
      }
      
      return $this->autoloader;
   }
   
   /**
    * Register the autoloader
    */
   public function register() {
      // registering autoload for phpoxide
      $autoloader = $this->getAutoloader();
      $autoloader->register();
      $dir = dirname(__FILE__);
		$autoloader->addNamespace('oxide',$dir);
   }
   
   /**
    * registerNamespaces function.
    * 
    * @access protected
    * @param array $namespaces
    * @return void
    */
   public function registerNamespaces(array $namespaces) {
      $autoloader = $this->getAutoloader();
      if($namespaces) {
         foreach($namespaces as $defs) {
            $dir = (isset($defs['dir'])) ? $defs['dir'] : trigger_error('dir key required for namespace.');
            $namespace = (isset($defs['namespace'])) ? $defs['namespace'] : trigger_error('namespace key required for namespace definition.');
            $autoloader->addNamespace($namespace, $dir);
         }
      }
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
	   static $boostrapping = null;
	   if($boostrapping) {
		   throw new \Exception("Bootstrapping already started.");
	   }
	   $boostrapping = true;
	   
      // register error handler
      // this is needed to get exception thrown for standard php errors
	   $errorhandler = new util\ErrorHandler();
	   $errorhandler->register();
	   
      // get singleton loader and register it.
      $loader = self::getInstance();
      $loader->register();
      
      // create the event notifier and share it
      $notifier = new util\EventNotifier();
      util\EventNotifier::setSharedInstance($notifier);   
      $notifier->notify(self::EVENT_BOOTSTRAP_START, null);
      
      // create the config manager and share it
      $configManager = new util\ConfigManager($config_dir);
      $config = $configManager->getConfig();
      util\ConfigManager::setSharedInstance($configManager);
      
      // creating the http context
      // set some default services
      $context = new http\Context(
         $request = http\Request::currentServerRequest((isset($config['base']) ? $config['base'] : null)), 
         new http\Response(),
         new http\Session([
               'cookie_domain' => $request->getUriComponents(http\Request::URI_HOST),
               'cookie_secure' => $request->isSecured()
            ])
         );
            
      //
      util\Debug::setResponse($context->getResponse());
      
      // configure the shared database conneciton
      data\Connection::setSharedInstance(function() use ($config){
         return new data\Connection($config->get('database', null, TRUE), [
            \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
            'FETCH_MODE'			=> \PDO::FETCH_ASSOC
         ]);
      });
      
      // configure helper container
      app\helper\HelperContainer::setSharedInstance(function() use ($context) {
         return new app\helper\HelperContainer($context);
      });
      
      // setup mailer
      util\Mailer::setSharedInstance(function() use ($config) {
         $options = isset($config['email']) ? $config['email'] : exception('Email configuration not set.');
         return new util\Mailer(true, new base\Dictionary($options));
      });
      
      // create the front controller and share it
      $fc = new http\FrontController($context);
      http\FrontController::setSharedInstance($fc);
      
      // load modules
      $modules = isset($config['modules']) ? $config['modules'] : exception("Modules are required.");
      $loader->loadModules($modules, $fc->getRouter());
      
      // load libraries
      $libraries = isset($config['libraries']) ? $config['libraries'] : null;
      if($libraries) $loader->registerNamespaces ($libraries);
      
      $notifier->notify(self::EVENT_BOOTSTRAP_END, null);
      
      // if autorun, run the front controller
		if($autorun) {
			$fc->run();
		}
		
		$boostrapping = false;
		return $fc;
   }
   
   
   /**
    * @param array $modules
    * @param \oxide\http\Router $router
    */
   protected function loadModules(array $modules, http\Router $router) {
      $autoloader = $this->getAutoloader();
      if($modules) {
         foreach($modules as $module) {
            $name = isset($module['name']) ? $module['name'] : exception('Module name is required.');
            $dir = isset($module['dir']) ? $module['dir'] : exception('Module dire is require.');
            $namespace = isset($module['namespace']) ? $module['namespace'] : exception('Module namespace is required.');
            $autoloader->addNamespace($namespace, $dir);
            $router->register($name, $dir, $namespace);
         }
      }
   }
}