<?php
namespace oxide\ui\html;
use oxide\ui\Renderer;


class Tag implements Renderer {
   use \oxide\base\pattern\PropertyAccessTrait;
   protected 
      $_tag,
      $_void = false;
   
   /**
    * Create a new Html $tag
    * 
    * @param string $tag
    * @param array $attributes
    */
   public function __construct($tag = null, $attributes = null) {
      if($tag) $this->_tag = $tag;
      if($attributes) $this->_t_property_storage = $attributes;
   }
   
   /**
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
    * Get the internal attributes array
    * 
    * This will return the pointer to the array
    * So changes to the return value will be reflected.
    * @return array
    */
   public function &getAttributes() {
      return $this->_t_property_storage;
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
      if($this->_void) $close = ' /';
      else $close = '';
      
      return '<'. $this->_tag . $this->attributeString($this->_t_property_storage) . $close . '>';
   }
   
   /**
    * 
    * @return string
    */
   public function renderClose() {
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