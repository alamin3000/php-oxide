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
      $isVoid = false,
           
      /**
       * @var Tag
       */
      $wrapperTag = null;
   
   protected
      /**
       * @var array
       */
      $attributes = [];
   
   
   protected 
      /**
       * @var string Name of the tag
       */
      $_tagName = null;
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag = null, $attributes = null, $void = false) {
      if($tag) $this->_tagName = $tag;
      if($attributes) $this->attributes = $attributes;
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
      if(isset($this->attributes[$key])) return $this->attributes[$key];
      else return $default;
   }
   
   /**
    * Set an attribute by the $key name
    * 
    * @param string $key
    * @param string $value
    */
   public function setAttribute($key, $value = null, $appendChar = null) {
      if($appendChar && isset($this->attributes[$key])) {
         $value = $this->attributes[$key] . $appendChar . $value;
      } else {
         $this->attributes[$key] = $value;
      }
   }
   
   /**
    * remove the given attribute $key
    * 
    * @param string $key
    */
   public function removeAttribute($key) {
      if($this->hasAttribute($this->attributes[$key])) unset($this->attributes[$key]);
   }
   
   /**
    * Checks if given attribute exits
    * 
    * @param string $key
    * @return bool
    */
   public function hasAttribute($key) {
      return array_key_exists($key, $this->attributes);
   }
   
   /**
    * Get the attribute array
    * 
    * @return array
    */
   public function getAttributes() {
      return $this->attributes;
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
      return (($this->wrapperTag) ? $this->wrapperTag->renderOpen() : '') .
               self::renderOpenTag($this->_tagName, $this->attributes, $this->isVoid);
   }
   
   /**
    * Render the close tag
    * 
    * If this tag is void, then it will simply return empty string.
    * @return string
    */
   final public function renderClose() {
      return self::renderCloseTag($this->_tagName, $this->isVoid).
           (($this->wrapperTag) ? $this->wrapperTag->renderClose() : '');
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
   
   /**
    * Attempt to convert given $arg to string
    * 
    * @param mixed $arg
    * @return string
    */
   public static function toString($arg) {
      if(is_scalar($arg)) return $arg;
      else if($arg instanceof \oxide\ui\Renderer) return $arg->render();
      else if(is_array ($arg) || $arg instanceof \Iterator) {
         $buf = '';
         foreach($arg as $val) {
            $buf .= self::toString($val);
         }
         return $buf;
      }
      else return (string) $arg;
   }
}