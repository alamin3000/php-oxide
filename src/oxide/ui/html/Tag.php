<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;

/**
 * 
 */
class Tag implements Renderer {
   public
      /**
       * @var bool Indicates if the current tag is void-tag or not
       */
      $isVoid = false;
   
   protected 
      /**
       * @var string Name of the tag
       */
      $_tagName = null,
           
      /**
       * @var Tag Tag to be wrapped
       */
      $_wrapperTag = null,
           
      /**
       * @var array Attributes
       */
      $_attributes = [];
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag = null, $attributes = null, $void = false) {
      if($tag) $this->_tagName = $tag;
      if($attributes) $this->_attributes = $attributes;
      $this->isVoid = $void;
   }
   
   /**
    * Set the $tag name
    * 
    * @param string $tag
    */
   public function setTagName($tag) {
      $this->_tagName = $tag;
   }
   
   /**
    * Get the Html tag name
    * 
    * @return string
    */
   public function getTagName() {
      return $this->_tagName;
   }
   
   /**
    * Get attribute by $key
    * 
    * If not found, $default value will be return
    * @param string $key
    * @param null|string $default
    * @return string
    */
   public function getAttribute($key, $default = null) {
      if(isset($this->_attributes[$key])) return $this->_attributes[$key];
      else return $default;
   }
   
   /**
    * Set an attribute by the $key name
    * 
    * @param string $key
    * @param string $value
    */
   public function setAttribute($key, $value = null, $appendChar = null) {
      if($appendChar && isset($this->_attributes[$key])) {
         $value = $this->_attributes[$key] . $appendChar . $value;
      } else {
         $this->_attributes[$key] = $value;
      }
   }
   
   /**
    * remove the given attribute $key
    * 
    * @param string $key
    */
   public function removeAttribute($key) {
      if($this->hasAttribute($this->_attributes[$key])) unset($this->_attributes[$key]);
   }
   
   /**
    * Checks if given attribute exits
    * 
    * @param string $key
    * @return bool
    */
   public function hasAttribute($key) {
      return array_key_exists($key, $this->_attributes);
   }
   
   /**
    * Get the attribute array
    * 
    * @return array
    */
   public function getAttributes() {
      return $this->_attributes;
   }
   
   /**
    * Sets attributes for the tag
    * 
    * Replaces current attributes.
    * @param array $attrs
    */
   public function setAttributes(array $attrs) {
      foreach($attrs as $key => $value) {
	      $this->_attributes[$key] = $value;
      }
      
      return $this;
   }
   
   /**
    * Removes all attributes from the tag
    * 
    * @return self fluent interface
    */
   public function clearAttributes() {
      $this->_attributes = [];
      
      return $this;
   }
   
   /**
    * Set Tag to wrap this tag
    * 
    * @return Tag
    */
   public function getWrapperTag() {
      return $this->_wrapperTag;
   }
   
   /**
    * Get current tag
    * 
    * @param Tag $tag
    */
   public function setWrapperTag(Tag $tag) {
      $this->_wrapperTag = $tag;
      
      return $this;
   }
   
   /**
    * Render the content
    * 
    * @return string
    */
   public function render() {
      return   $this->renderOpen() . 
               $this->renderClose(); 
   }
   
   /**
    * Render the opening tag
    * 
    * If the tag is void tag, then it will self close
    * @return string
    */
   final public function renderOpen() {
      if($this->_wrapperTag) 
         $wrapperOpen = $this->_wrapperTag->renderOpen();
      else
         $wrapperOpen = '';
      
      return $wrapperOpen.self::renderOpenTag($this->_tagName, $this->_attributes, $this->isVoid);
   }
   
   /**
    * Render the close tag
    * 
    * If this tag is void, then it will simply return empty string.
    * @return string
    */
   final public function renderClose() {
      if($this->_wrapperTag) 
         $wrapperClose = $this->_wrapperTag->renderClose();
      else
         $wrapperClose = '';
      return self::renderCloseTag($this->_tagName, $this->isVoid).$wrapperClose;
   }
   
   /**
    * Render the tag with given $content
    * 
    * @param mixed $content
    * @return string
    */
   public function renderWithContent($content) {
      return $this->renderOpen() .
         $content.
         $this->renderClose(); 
   }
   
   /**
    * Convert array into key=value string
    * 
    * @param array $attributes
    * @return string Will always return an extra empty space
    */
   static public function attributeString(array $attributes = null) {
      if(!$attributes) return '';
		
      $str = '';
      foreach ($attributes as $key => $value) {
         if($value === null) {
            $str .= $key . ' ';
         } else {
            if(!is_scalar($value)) {
               trigger_error('both value for attribute key {' . $key . '} must be scalar data type');
            }
            $value = self::escape($value);
            $str .= "{$key}=\"{$value}\" ";
         }
      }
      
      return ' ' . trim($str);
   }
     
   /**
    * Escape for html entities
    * 
    * @param string $string
    * @return string
    */
   static public function escape($string) {
      return htmlentities($string, ENT_QUOTES);
   }
   
   /**
    * Render open tag
    * 
    * @param string $tagName
    * @param array $attributes
    * @param bool $void
    * @return string
    */
   static public function renderOpenTag($tagName, array $attributes = null, $void = false) {
      if(!$tagName) return '';
      return '<'. $tagName . self::attributeString($attributes) . 
              (($void) ? ' /' : '') . 
              '>';
   }
   
   /**
    * Render close tag
    * 
    * @param string $tagName
    * @param bool $void
    * @return string
    */
   static public function renderCloseTag($tagName, $void = false) {
      return ($tagName && !$void) ? '</'.$tagName.'>' : '';
   }
}