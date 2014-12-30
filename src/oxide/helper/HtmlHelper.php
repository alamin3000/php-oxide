<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\helper;

class HtmlHelper extends HelperAbstract {   
   /**
    * 
    * @param type $tag
    * @param array $attributes
    * @return type
    */
   public function openTag($tag, array $attributes = null, $void = false) {
      $close_tag = '';
      if($void)  $close_tag = " /";

      // rendering the markup
      return sprintf('<%s%s%s>', 
         $tag, 
         $this->attributeString($attributes),
         $close_tag);
   }
   
   /**
    * 
    * @param type $tag
    * @return type
    */
   public function closeTag($tag, $void = false) {
      if($void) return '';
      return "</{$tag}>";
   }
   
   /**
    * 
    * @param type $tag
    * @param type $inner
    * @param array $attributes
    * @return type
    */
   public function tag($tag, $inner = null, array $attributes = null, $void = false) {
      return $this->openTag ($tag, $attributes, $void) .
           $inner .
           $this->closeTag ($tag, $void);
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
    * 
    * @param array $attributes
    * @return string
    */
   public function attributeString(array $attributes = null) {
      if(!$attributes) return '';
		
      $str = '';
      foreach ($attributes as $key => $value) {
         if(!empty($value) && !is_scalar($value)) {
            trigger_error('both value for attribute key {' . $key . '} must be scalar data type');
         }
         $value = $this->escape($value);
         $str .= "{$key}=\"{$value}\" ";
      }
      
      return ' ' . trim($str);
   }
}