<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;


class Tag implements Renderer {
   public 
      /**
       * @var bool Indicates if the tag is void tag or not
       */
      $void = false,
           
      /**
       * @var array Tag's attribute array
       */
      $attributes = [];
   
   protected 
      $_wrappers = null,
      $_tag;
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag = null, $attributes = null, $void = false) {
      if($tag) $this->_tag = $tag;
      if($attributes) $this->_t_property_storage = $attributes;
      if($void) $this->_void = $void;
   }
   
   /**
    * Indicates if the tag is voided
    * @param bool $bool
    * @return bool
    */
   public function void($bool = null) {
      if($bool === null) return $this->_void;
      else $this->_void = (bool) $bool;
   }
   
   /**
    * Add wrapper tags for this tag
    * 
    * These wrappers will be wrapped at the end of rendering
    * @param array $tags
    * @return type
    */
   public function wrappers(array $tags = null) {
      if($tags) $this->_wrappers = $tags;
      else return $this->_wrappers;
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
   public function getAttr($key, $default = null) {
      if(isset($this->attributes[$key])) return $this->attributes[$key];
      else return $default;
   }
   
   /**
    * Set an attribute by the $key name
    * 
    * @param type $key
    * @param type $value
    */
   public function setAttr($key, $value = null) {
      $this->attributes[$key] = $value;
   }
   
   /**
    * remove the given attribute $key
    * 
    * @param string $key
    */
   public function removeAttr($key) {
      if(isset($this->attributes[$key])) unset($this->attributes[$key]);
   }
   
   /**
    * Checks if given attribute exits
    * 
    * @param string $key
    * @return bool
    */
   public function hasAttr($key) {
      return isset($this->attributes[$key]);
   }
   
   /**
    * Convert array into key=value string
    * 
    * @param array $attributes
    * @return string
    * @throws \Exception
    */
   public function attributeString(array $attributes = null) {
      if(empty($attributes)) return '';
		
      $str = '';
      foreach ($attributes as $key => $value) {
         if(!empty($value) && !is_scalar($value)) {
            trigger_error('both value for attribute key {' . $key . '} must be scalar data type');
         }
         $value = self::escape($value);
         $str .= "{$key}=\"{$value}\" ";
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
      $str = '';
      if($this->_wrappers) {
         foreach($this->_wrappers as $wrapper) {
            $str .= $wrapper->renderOpen();
         }
      }
      if($this->_void) $close = ' /';
      else $close = '';
      
      $str = '<'. $this->_tag . $this->attributeString($this->_t_property_storage) . $close . '>';
      return $str;
   }
   
   /**
    * 
    * @return string
    */
   public function renderClose() {
      $str = '';
      if($this->_wrappers) {
         foreach($this->_wrappers as $wrapper) {
            $str .= $wrapper->renderClose();
         }
      }
      if($this->_void) return '';
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
   public function renderWithContent($content) {
      return $this->renderOpen() .
            $content.
            $this->renderClose(); 
   }
}