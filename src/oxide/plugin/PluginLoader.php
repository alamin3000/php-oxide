<?php
namespace oxide\plugin;
use oxide\http\Context;
use oxide\util\Loader;
use oxide\util\EventNotifier;


/**
 * Plugin Loader
 *
 * responsible for loading plugins
 * @package oxide
 * @subpackage plugin
 */
class PluginLoader
{
	protected 
      /**
       * @var array stores loaded plugins in memory
       */
		$_plugins = [];
   
   /**
    * Loads plugins from the provided module, if available
    * 
    * No exception thrown if plugin is not found.
    * @param type $modules
    * @param \oxide\http\Context $context
    */
   public function loadModulePlugins($modules, Context $context)
   {
      foreach ((array) $modules as $name => $dir) {
         $plugindir = $dir . '/plugin';         
         $this->loadPlugin($name, $plugindir, $context);
      }
   }
   
   /**
    * Loads a single plugin
    * 
    * @param type $name
    * @param type $dir
    * @param \oxide\http\Context $context
    * @return boolean
    * @throws \Exception
    */
   public function loadPlugin($name, $dir, Context $context)
   {
      // first check if another plugin with same name already registered
      // if so, this would throw an exception
      if(isset($this->_plugins[$name])) {
         throw new \Exception("Plugin with name: $name already exists.");
      }
      
      $classname = ucfirst($name);
      if(strpos($classname, 'Plugin') === FALSE) {
         $classname .= 'Plugin';
      }

      // load the plugin class
      if(!Loader::loadClass($classname, $dir)) {
         return false;
      }

      if(!Loader::getDirectoryWithKey($name)) {
         // if there is already directory under this name
         // do not add it
         Loader::addDirectory($dir, $name);
      }
      
      // create the plugin
      $plugin = new $classname($context);

      // make sure plugin is instance of Command
      if(!($plugin instanceof Pluggable)) {
         throw new \Exception ("Plugin $name must implement Command interface.");
      }

      // add the plugin
      // we should add before executing, just in plugin wants to unload itself
      $this->_plugins[$name] = $plugin;         

      // execute the plugin
      $plugin->plug($context);
      
      return true;
   }
		
	/**
	 * @todo check if class name already has 'Plugin' if so don't append it.
	 * @param object $plugins
	 * @return 
	 */
	public function loadPlugins($plugins, Context $context)
	{
		foreach((array) $plugins as $name => $dir) {
         if(!$this->loadPlugin($name, $dir, $context)) {
            throw new \Exception('Unable to load plugin: ' . $name . ', location: ' . $dir);
         }
		}
	}
   
   public function load($name) {
      
   }
   
   /**
    * 
    * @param string $name name of plugin
    */
   public function unLoad($name)
   {
      if(isset($this->_plugins[$name])) {
         $plugin = $this->_plugins[$name];
         $notifier = EventNotifier::defaultInstance();
         $notifier->unregisterListener($plugin);
         unset($this->_plugins[$name]);
      }
   }
}