<?php
namespace oxide\plugin;
use oxide\http\Context;
use oxide\util\ConfigIni;
use oxide\util\EventNotifier;
use oxide\util\ConfigFile;
use ReflectionClass;

/**
 * Plugin Abstract
 *
 * @package oxide
 * @subpackage plugin
 */
abstract class PluginCommand implements Pluggable {
   protected 
      $_name = null,
      $_dir = null,
      $_config_dir = null,
      $_config_file = 'config.ini',
		$_config = null,
		$_context = null,
		$_eventMethodPrefix = 'on',
		$_eventMethodSuffix = '';
   
	/**
	 * construction
	 * 
	 * plugins should call parent construction when overriding the construction
	 * @param Context $context
	 */
	final public function __construct(Context $context) {
		$this->_context = $context;
      $class = get_called_class();
      $this->_name = substr($class, 0, strlen($class) - strlen('Plugin'));
      
      $reflector = new ReflectionClass(get_called_class());
     	$dir = realpath(dirname($reflector->getFileName()));
      $this->_dir = $dir;
	}


   /**
    * Get the current application context
    * @return Context 
    */
	public function getContext() {
		return $this->_context;
	}
   
   public function getName() {
      return $this->_name;
   }
      
   /**
    * Get plugin config file
    * 
    * @return ConfigIni 
    */
   public function getConfig() {
      if($this->_config == null) {
         $config_file =  $this->_plugin_dir . "/{$this->_config_file}";      
         if(is_file($config_file)) {
            $this->_config = new ConfigFile($config_file);
         } else {
            throw new \Exception("Plugin config file not found: $config_file");
         }
      }
      
		return $this->_config;
   }

	/**
    * Generate module name based on $event
    * 
    * @param string $event
    * @return string 
    */
   protected function _getMethodName($event) {
   	return $this->_eventMethodPrefix . $event . $this->_eventMethodSuffix;
   }
   
   /**
    * 
    * @return string
    */
   public function getDirectory() {
      return $this->_dir;
   }

   /**
	 * registers for given event
	 * 
	 * if $method is given, then that particular method will be used
	 * else $event name will be used as method name
	 * 
	 * @return 
	 * @param string $event
	 * @param string $method[optional]
	 */
	protected function register($event, $method = null, $object = null) {
		if(!$method) {
			$method = $this->_getMethodName($event);
		}
      
      EventNotifier::defaultInstance()->register($event, [$this, $method], $object);
	}	
}