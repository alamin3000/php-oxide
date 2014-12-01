<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;
use oxide\std\Dictionary;

class Page implements Renderer {
   public
      $prerender = false,
      $title = null;
           
   protected 
      $_scopeObject = null,
      $_cache = null,
      $_partials = [],
      $_data = null,
      $_script = null;
   
   /**
    * 
    * @param string $script
    */
   public function __construct($script = null, Dictionary $data = null) {
      $this->_script = $script;
      if($data) {
         $this->setData($data);
      }
   }
   
   /**
    * Set data for the page script
    * 
    * @param Dictionary $data
    */
   public function setData(Dictionary $data) {
      $this->_data = $data;
   }
   
   /**
    * 
    * @return Dictionary
    */
   public function getData() {
      return $this->_data;
   }
   
      
   /**
    * 
    * @param \oxide\ui\Renderer $renderer
    * @param type $key
    */
   public function addPartial(Renderer $renderer, $key) {
      $this->_partials[$key] = $renderer;
   }
   
   /**
    * 
    * @param type $key
    * @return type
    */
   public function getPartial($key) {
      if(isset($this->_partials[$key])) {
         return $this->_partials[$key];
      }
      
      return null;
   }
   
   /**
    * Render a given partial with $key
    * 
    * @param string $key
    * @return string
    */
   public function renderPartial($key) {
      $partial = $this->getPartial($key);
      if($partial) {
         return $partial->render();
      }
      
      return null;
   }

   /**
	 * sets the view script to render
	 *
	 * this function maybe used to change script for rendering
	 * this function must be called prior to outputting the content.
	 * @param $script string
	 */
	public function setScript($script) {
		$this->_script = $script;
	}
	
	/**
	 * get the current view script
	 * @return string
	 */
	public function getScript() {
		return $this->_script;
	}
   
   public function setScriptObjectScope($object) {
      
   }
   
   public function getScriptObjectScope() {
      
   }

    /**
    * Executes the script in private scope
    * @param string $script
    * @param array $data
    */
   protected function renderPage($script, Dictionary $data) {
      if(!file_exists($script)) {
         trigger_error("View script '{$script}' not found", E_USER_ERROR);
      }
      
      
      include $script;
   }
   
   public function render() {
      if($this->_cache === null) {
         $script = $this->getScript();
         $data = $this->_data;

         // prerender partials
         foreach($this->_partials as $partial) {
            if($partial instanceof Page) {
               if($partial->prerender)
                  $partial->render();
            }
         }

         ob_start();
         echo $this->renderPage($script, $data);
         $this->_cache = ob_get_clean();
      }
      
      return $this->_cache;
   }
}