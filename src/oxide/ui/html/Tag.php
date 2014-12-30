<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;
use oxide\helper\_html;


class Tag implements Renderer {
   protected 
      $_tag,
      $_void = false,
      $_attributes = [];
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag, $attributes = null) {
      $this->_tag = $tag;
      if($attributes) $this->_attributes = $attributes;
   }
   
   /**
    * Get the Html tag name
    * 
    * @return type
    */
   public function getTag() {
      return $this->_tag;
   }
   
   /**
    * Sets an attribute for the tag
    * 
    * @param string $attrib
    * @param string $value
    */
   public function setAttribute($attrib, $value) {
      $this->_attributes[$attrib] =  $value;
   }
   
   /**
    * get an attribute from the element
    * @param string $attrib
    * @param string $default_value if attribute is not found, this value will be returned
    * @return string
    */
   public function getAttribute($attrib, $default_value = '') {
      if($this->__isset($attrib)) {
         return $this->__get($attrib);
      } else {      	
         return $default_value;
      }
   }
   
   /**
    * Gets all current attributes for the tag
    * 
    * @return array
    */
   public function getAttributes() {
      return $this->_attributes;
   }

	/**
	 * add multiple attributes at once
	 * @param array $attributes
	 */
   public function setAttributes(array $attributes) {
      foreach($attributes as $attrib => $value) {
         $this->_attributes[$attrib] = $value;
      }
      return $this;
   }
   
   /**
    * Indicates whether or not any attributes for the element exists
    * 
    * @return bool
    */
   public function hasAttributes() {
      return count($this->_attributes) > 0;
   }
   
	public function __set($key, $value) {
      $this->_attributes[$key] = $value;
	}
	
	public function __get($key) {
      $value = $this->_attributes[$key];
      if(is_array($value)) {
         return $value;
      } else {
         return $value;
      }
	}
   
   public function __isset($key) {
      return isset($this->_attributes[$key]);
   }
   
   public function __unset($key) {
      unset($this->_attributes[$key]);
   }
   
   /**
    * Render the opening tag
    * 
    * If the tag is void tag, then it will self close
    * @see oxide\helper\Html::rstart()
    * @return type
    */
   public function renderOpen() {
      return HtmlHelper::openTag($this->_tag, $this->_attributes, $this->_void);
   }
   
   /**
    * 
    * @return type
    */
   public function renderClose() {
      return HtmlHelper::closeTag($this->_tag, $this->_void);
   }
      
   public function render() {
      return $this->renderOpen() .
        $this->renderClose();
   }
   
   public static function renderOpenTag($tag, array $attributes = null, $void = false) {
      return _html::openTag($tag, $attributes, $void);
   }
   
   public static function renderCloseTag($tag, $void = false) {
      return HtmlHelper::closeTag($tag, $void);
   }
   
   public static function escape($string) {
      return HtmlHelper::escape($string);
   }
   
   /**
    * Allows to render an element based on given $tag and $content
    * 
    * @param \oxide\ui\html\Tag $tag
    * @param type $content
    */
   public static function renderTag($tag, $content = null, array $attributes = null, $void = false) {
      if($tag instanceof self) {
         return $tag->renderOpen() .
            $content.
            $tag->renderClose();    
      } else {
         return self::renderOpenTag($tag, $attributes, $void) .
              $content .
              self::renderCloseTag($tag, $void);

      }
   }     
}