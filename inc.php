<?php
use oxide\util\Loader;
use oxide\util\ConfigIni;
use oxide\http;
use oxide\plugin\PluginLoader;


/**
 * defines phpoxide directory location
 *
 * This is the root directory for complete phpoxide engine, including oxide framework
 */
define("PHPOXIDE_DIR", dirname(__FILE__) );

/**
 * defines phpoxide library directory
 *
 * convineint defination for phpoxide library directory
 */
define("PHPOXIDE_DIR_LIBRARY", PHPOXIDE_DIR . '/library');

/**
 * defines phpoxide module directory
 *
 * convinient defination for phpoxide module directory
 */
define("PHPOXIDE_DIR_MODULE", PHPOXIDE_DIR . '/module');

/**
 * defines phpoxide public version number
 */
define('PHPOXIDE_VERSION', '0.6b');

/**
 * defines the public root directory for the website
 */
define('PHPOXIDE_PUBLIC_ROOT', $_SERVER['DOCUMENT_ROOT']);

/**
 * content view identification
 */
define("PHPOXIDE_CONTENT_VIEW", 'content');
define("TEMPLATE_CONTENT_VIEW", PHPOXIDE_CONTENT_VIEW);


// include phpoxide directories in php include path
// this may not work under some web server configuration
// use Loader::load() instead of require_once
ini_set("include_path",
   ini_get("include_path") . PATH_SEPARATOR .
      PHPOXIDE_DIR_LIBRARY . PATH_SEPARATOR .
      PHPOXIDE_DIR_LIBRARY . '/PEAR'
   );


// loading some required utility functions and front controller library
require_once PHPOXIDE_DIR_LIBRARY . '/oxide/util/Loader.php';
spl_autoload_register('\oxide\util\Loader::autoLoad');

// register phpoxide directory with the loader
Loader::addLoadDir(PHPOXIDE_DIR_LIBRARY);
Loader::addLoadDir(PHPOXIDE_DIR);


/*
 * REQUEST_URI fix for non apache server
 * code take from: http://www.dreamincode.net/code/snippet103.htm
 * @todo need to validate
 */
if(!isset($_SERVER['REQUEST_URI'])) {
	if(isset($_SERVER['SCRIPT_NAME']))
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	else
		$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
		
	if($_SERVER['QUERY_STRING']) {
	  $_SERVER['REQUEST_URI'] .=  '?'.$_SERVER['QUERY_STRING'];
	}
}


/**
 * phpoxide main function
 *
 * configures and starts the web engine
 * uses config ini file to configure and uses the FrontController to start the engine.
 * @param mixed $args
 */
function phpoxide_main($args = null)
{
	if(!defined("APPLICATION_DIR")) { die('APPLICATION_DIR must be defined.'); }
	Loader::addLoadDir(APPLICATION_DIR);
   
   
   // First we create the main application context
   // this will be application level scope for storing data and services for the 
   // entire application
   $context = null;
   if(!isset($args['context'])) { 
      // get the default context
      $context = http\Context::defaultInstance();
   } else {
      $context = $args['context'];
   }

   // STEP 1: CREATE CONFIG OBJECT
	// main configuration file name
   // this should be located on the root config folder by the config.ini name
	/*
	 * checks for some of the required
	 *
	 * we need to make sure some of the required constants are defined
	 * we need to make sure that application config directory is defined
	 */
   if(!defined("APPLICATION_CONFIG_DIR")) {
      define("APPLICATION_CONFIG_DIR", rtrim(APPLICATION_DIR, '/') . '/config');
   }
   $config_file =  rtrim(APPLICATION_CONFIG_DIR, '/') . '/config.ini';
   $config = new ConfigIni(realpath($config_file), true);
   ConfigIni::setDefaultInstance($config); // setting the main configuration as default config
   $context->setConfig($config); // add the config object to the default context

	// setup default timezone
	$default_timezone = $config->getSectionValue('site', 'timezone', null);
	if($default_timezone) {
		 date_default_timezone_set($default_timezone);
	}

   // STEP 2: SETUP DEBUG ENVIRONMENT
   // check if debug mode is enabled
   // if so display all errors
   $debug =  (bool) (isset($config->debug) && isset($config->debug->display)) ? $config->debug->display : true;
   if($debug) {
      \oxide\util\Debug::enable();
   }

   // STEP 3: REGISTER MODULES
	$module_dirs = array_unique((array) $config->module);
   foreach($module_dirs as $name => $dir) {
      $path = realpath($dir);
//      $config->module->$name = $dir;

      // add it to the loader directory array
      // this will allow to load using Loader::load() method
      Loader::addDirectory($path, $name);
      
      // creates module directory constant
      // this will be convinient to access different module dir
      // MODULE_{MODULENAME}_DIR
      define("MODULE_" . strtoupper($name) . "_DIR", $path );
   }

   // STEP 5: LOAD PLUGINS
	// now load plugins
	$pluginLoader = new PluginLoader();
   // first we will attempt to load plugins for modules
   // each module is allowed to have one plugin with the same name for auto loading
   $pluginLoader->loadModulePlugins($module_dirs, $context);
   // now we will load the plugins
	$plugins = $config->getSection('plugin');
	$pluginLoader->loadPlugins($plugins, $context);

   
   // STEP 6: START ENGINE
   // front controller processing start
   // create the default instance and execute
   $fc = http\FrontController::defaultInstance(); 
   $fc->execute($context);
}


/*
 * auto start phpxodie engine
 *
 * if auto run is defined, then phpoxide will use default phpoxide_main() method to start the web engine
 * otherwise, phpoxide_main() must be called manually,
 * or alternatly web engine may be configured and started manually.
 */
if(defined("PHPOXIDE_AUTO_RUN") && PHPOXIDE_AUTO_RUN == false) {
   // don't do anything
} else {
   // auto run
   phpoxide_main(null);
}