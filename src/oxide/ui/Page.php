<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;
use oxide\base\Dictionary;

class Page implements Renderer, \ArrayAccess {
   protected
      $data = null;
   
   protected
      $_prerenders = [],
      $_parent = null,
      $_cache = null,
      $_partials = [],
      $_codeScript = null,
      $_script = null;
   
   /**
    * 
    * @param string $script
    */
   public function __construct($script = null, \ArrayObject $data = null, $codeScript = null) {
      $this->_script = $script;
      if($data) {
         $this->setData($data);
      }
      if($codeScript) $this->setCodeScript ($codeScript);
   }
   
   /**
    * Set data for the page script
    * 
    * @param Dictionary $data
    */
   public function setData(\ArrayObject $data) {
      $this->data = $data;
   }
   
   
   /**
    * getData function.
    * 
    * @access public
    * @param mixed $key (default: null)
    * @return void
    */
   public function getData() {      	   
      return $this->data;
   }
   
   /**
    * Set the behind the page code script
    * @param string $script
    */
   public function setCodeScript($script) {
      $this->_codeScript = $script;
   }
   
   /**
    * Set the parent container for the page
    * 
    * @param \oxide\ui\Renderer $parent
    */
   public function setParent(Renderer $parent) {
      $this->_parent = $parent;
   }
   
   /**
    * Get the parent container for the page if any
    * 
    * @return type
    */
   public function getParent() {
      return $this->_parent;
   }
      
   /**
    * 
    * @param \oxide\ui\Renderer $renderer
    * @param type $key
    */
   public function addPartial(Renderer $renderer, $key) {
	   if($renderer instanceof self) $renderer->setParent($this);
      $this->_partials[$key] = $renderer;
   }
   
   /**
    * 
    * @param type $key
    * @return type
    */
   public function hasPartial($key) {
      return array_key_exists($key, $this->_partials);
   }
   
   /**
    * 
    * @param type $key
    * @return type
    */
   public function getPartial($key) {
      if(isset($this->_prerenders[$key])) {
         return $this->_prerenders[$key];
      } else if(isset($this->_partials[$key])) {
         return $this->_partials[$key];
      }
      
      return '';
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
   
   /**
    * Executes the script in private scope
    * @param string $script
    * @param array $data
    */
   protected function renderPage($script, Dictionary $data = null) {
      ob_start();
      if(!file_exists($script)) {
         trigger_error("View script '{$script}' not found", E_USER_ERROR);
      }
            
      include $script;
      return ob_get_clean();
   }
   
   /**
    * 
    * @return string
    */
   public function render() {
      $script = $this->getScript();
      $data = $this->data;

      // first execute the behind the page code if available
      if($this->_codeScript) {
         $this->renderPage($this->_codeScript, $data); // not expected to return anything
      }

      // prerender partials
      foreach($this->_partials as $key => $partial) {
         $this->_prerenders[$key] = $partial->render();
      }
      
      return $this->renderPage($script, $data);
   }
   
   public function offsetExists($offset) {
      return isset($this->data[$offset]);
   }
   
   public function offsetGet($offset) {
      return $this->data[$offset];
   }
   
   public function offsetSet($offset, $value) {
      $this->data[$offset] =  $value;
   }
   
   public function offsetUnset($offset) {
      unset($this->data[$offset]);
   }
   
   public function __set($name, $value) {
      $this->data->{$name} = $value;
   }
   
   public function __get($name) {
      return $this->data->{$name};
   }
   
   public function __isset($name) {
      return isset($this->data->{$name});
   }
   
   public function __unset($name) {
      unset($this->data->{$name});
   }
}