<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;


class Tag implements Renderer {
   public 
      /**
       * @var bool Indicates if the tag is void tag or not
       */
      $void = false;
   
   protected 
      $_attributes = [],
      $_tag = null;
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag = null, $attributes = null, $void = false) {
      if($tag) $this->_tag = $tag;
      if($attributes) $this->_attributes = $attributes;
      if($void) $this->void = $void;
   }
      
   /**
    * Set the $tag name
    * 
    * @param type $tag
    */
   public function setTag($tag) {
      $this->_tag = $tag;
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
    * Get attribute by $key
    * 
    * If not found, $default value will be return
    * @param type $key
    * @param type $default
    * @return type
    */
   public function getAttribute($key, $default = null) {
      if(isset($this->_attributes[$key])) return $this->_attributes[$key];
      else return $default;
   }
   
   /**
    * Set an attribute by the $key name
    * 
    * @param type $key
    * @param type $value
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
      if(isset($this->_attributes[$key])) unset($this->_attributes[$key]);
   }
   
   /**
    * Checks if given attribute exits
    * 
    * @param string $key
    * @return bool
    */
   public function hasAttribute($key) {
      return isset($this->_attributes[$key]);
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
      $this->_attributes = $attrs;
   }
   
   /**
    * Convert array into key=value string
    * 
    * @param array $attributes
    * @return string
    * @throws \Exception
    */
   public function attributeString() {
      if(empty($this->_attributes)) return '';
		
      $str = '';
      foreach ($this->_attributes as $key => $value) {
         if($value === null) {
            $str .= "$key ";
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
    * 
    * @param type $string
    * @return type
    */
   public function escape($string) {
      return htmlentities($string, ENT_QUOTES);
   }
   
   /**
    * Render the opening tag
    * 
    * If the tag is void tag, then it will self close
    * @see oxide\helper\Html::rstart()
    * @return string
    */
   public function renderOpen() {
      if(!$this->_tag) return '';
      if($this->void) $close = ' /';
      else $close = '';
      
      return '<'. $this->_tag . $this->attributeString() . $close . '>';
   }
   
   /**
    * 
    * @return string
    */
   public function renderClose() {
      if(!$this->_tag) return '';
      if(!$this->void) 
         return "</{$this->_tag}>";
   }
      
   /**
    * 
    * @return string
    */
   public function render() {
      return $this->renderOpen() .
        $this->renderClose();
   }
   
   /**
    * Render the tag with given $content
    * 
    * @param type $content
    * @return type
    */
   public function renderContent($content) {
      return $this->renderOpen() .
            $content.
            $this->renderClose(); 
   }
}