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
	   static $boostrapping = false;
	   if($boostrapping) {
		   throw new \Exception("Bootstrapping already started.");
	   }
	   $boostrapping = true;
	   
      $loader = self::getInstance();
      $loader->register();
      
      // create the event notifier and share it
      $notifier = new util\EventNotifier();
      util\EventNotifier::setSharedInstance($notifier);   
      $notifier->notify(self::EVENT_BOOTSTRAP_START, null);
      
      // create the config manager and share it
      $configManager = new app\ConfigManager($config_dir);
      $config = $configManager->getConfig();
      app\ConfigManager::setSharedInstance($configManager);
      
      // creating the http context
      // set some default services
      // share the context
      $request = http\Request::currentServerRequest();
      if(isset($config['base'])) { // set the request base url path, if provided
	      $request->setBase($config['base']);
	   }
      $context = new http\Context(
            $request, 
            new http\Response());
      util\Debug::setResponse($context->getResponse());
      
      // set session
      $context->addResolver('session', function(http\Context $container) {
         $request = $container->get('request');
         $opt = [
            'cookie_domain' => $request->getUriComponents(http\Request::URI_HOST),
            'cookie_secure' => $request->isSecured()
         ];
         return new http\Session(null, $opt);
      });
      
      // setup the authentication
      $context->addResolver('auth', function($container) {
         return new app\auth\Authenticator(
                 new app\auth\SessionStorage($container->get('session')));
      });
      
      // configure the shared database conneciton
      data\Connection::setSharedInstance(function() use ($config){
         return new data\Connection($config->get('database', null, TRUE), [
            \PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
            'FETCH_MODE'			=> \PDO::FETCH_ASSOC
         ]);
      });
      
      // setup default mailer instance
      util\Mailer::setSharedInstance(function() use ($config) {
         $config = $c->getConfig();
         $type = $config->getUsingKeyPath('email.transport', null, true);
         if($type == 'smtp') {
            $host       = $config->getUsingKeyPath('email.options.host', null, true);
            $port       = $config->getUsingKeyPath('email.options.port', 25);
            $encrypt    = $config->getUsingKeyPath('email.options.encrypt', null);
            $username   = $config->getUsingKeyPath('email.options.username', null, true);
            $password   = $config->getUsingKeyPath('email.options.password', null, true);
            $transport  = \Swift_SmtpTransport::newInstance($host, $port, $encrypt);
            $transport->setUsername($username);
            $transport->setPassword($password);
         } else if($type == 'sendmail') {
            $args = $config->getusingKeyPath('email.options.command', null);
            $transport = \Swift_SendmailTransport::newInstance($args);
         } else if($type == 'mail') {
            $transport = \Swift_MailTransport::newInstance();
         } else {
            throw new \Exception('Email transport is not recognized.');
         }
         
         return \Swift_Mailer::newInstance($transport);
      });
      
      // create the front controller and share it
      $fc = new http\FrontController($context);
      http\FrontController::setSharedInstance($fc);
      
      // load modules
      $modules = $config->get('modules', NULL, TRUE);
      $loader->loadModules($modules, $fc->getRouter());
      
      // load libraries
      $libraries = $config->get('libraries');
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
            $dic = new base\Dictionary($module);
            $name = $dic->get('name', NULL, TRUE);
            $dir = $dic->get('dir', NULL, TRUE);
            $namespace = $dic->get('namespace', NULL, TRUE);
            $autoloader->addNamespace($namespace, $dir);
            $router->register($name, $dir, $namespace);
         }
      }
   }
}