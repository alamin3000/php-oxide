<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app;
use oxide\ui\Renderer;
use oxide\base\Stringify;

class View implements Renderer, Stringify {
   public
      $identifier = null,
      $title = null;
   
   protected
      $_rendering = false,
      $_cache = null,
      $_contentType = 'text/html',
      $_encoding = 'utf-8',
      $_data = null,
      $_subviews = [],
      $_isRendering = false,
      $_renderer = null;
   
   
   /**
    * Construct
    * 
    * NOTE: for subclasses, calling parent::__construct() you can pass $this
    * @param Renderer $renderer
    */
   public function __construct(Renderer $renderer = null) {
      if($renderer)
         $this->_renderer = $renderer;
   }
   
   /**
    * 
    * @param ViewData $data
    */
   public function setData(ViewData $data) {
      $this->_data = $data;
   }
   
   /**
    * 
    * @return ViewData
    */
   public function getData() {
      return $this->_data;
   }
   
   /**
    * Checks to see if view is currently being rendered
    * 
    * @return boolean
    */
   public function isRendering() {
      return $this->_rendering;
   }
   
   /**
    * Set the content type for the view
    * 
    * @param string $type
    */
   public function setContentType($type) {
      $this->_contentType = $type;
   }
   
   /**
    * Get the render type for the view
    * 
    * @return string
    */
   public function getContentType() {
      return $this->_contentType;
   }
   
   /**
    * Set the encoding for the view
    * 
    * @param string $encoding
    */
   public function setEncoding($encoding) {
      $this->_encoding = $encoding;
   }
   
   /**
    * Get the encoding for the view
    * @return string
    */
   public function getEncoding() {
      return $this->_encoding;
   }
   
   /**
    * Set the renderer for the view
    * 
    * @param Renderer $renderer
    */
   public function setRenderer(Renderer $renderer) {
      $this->_renderer = $renderer;
   }
   
   /**
    * Get the Renderer object for the view
    * 
    * @return Renderer
    */
   public function getRenderer() {
      return $this->_renderer;
   }
   
   public function render() {
      if($this->_cache === null) {
         if($this->_isRendering) { // rendering within rendering :/
            trigger_error("View ({$this->identifier}) is already rendering.", E_USER_ERROR);
         }
         $this->_isRendering = true;
         $this->_cache = $this->performRender();
         $this->_isRendering = false;
      }
      
      return $this->_cache;
   }
   
   /**
    * Perform actual rendering
    * 
    * Subclasses should override this method instead of render()
    * @return string
    */
   protected function performRender() {
      $renderer = $this->getRenderer();
      if($renderer) {
         return $renderer->render();
      }
      
      return null;
   }
   
   /**
    * Get the rendered string
    * 
    * Always uses cache if available
    * @return string
    */
   public function __toString() {
      return $this->render();
   }
   
   public function __call($name, $arguments) {
      $renderer = $this->_renderer;
      
      if(is_callable([$renderer, $name])) {
         return call_user_func_array([$renderer, $name], $arguments);
      } else {
         throw new \InvalidArgumentException('Method not found: ' . get_class($renderer) . '::' . $name);
      }
   }
   
   public function __get($property) {
      $renderer = $this->_renderer;
      if (property_exists($renderer, $property)) {
         return $renderer->$property;
      }
      return null;
    }

   public function __set($property, $value) {
      $this->_renderer->$property = $value;
      return $this;
   }
}