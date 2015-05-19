<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\ui\html\Tag;
use oxide\ui\html\Element;

class Head extends Element {
   public 
      $title = null,
      $stylesheets = [],
      $metas = [],
      $styles = [],
      $scripts = [];
   
   public function __construct() {
      parent::__construct('head');
   }
   /**
    * 
    * @param string $string
    */
   public function title($string) {
      $this['title'] = new Element('title', $string);
   }
   
   /**
    * 
    * @param type $name
    * @param type $content
    * @param type $nameKey
    * @param type $contentKey
    */
   public function meta($name, $content, $nameKey = 'name', $contentKey = 'content') {
      $this[] = new Tag('meta', [$nameKey => $name, $contentKey => $content]);
   }
   
   /**
    * 
    * @param type $href
    * @param type $rel
    * @param array $attr
    */
   public function link($href, $rel, array $attr = null) {
      if(!$attr) $attr = [];
      $attr['href'] = $href;
      $attr['rel'] = $rel;
      
      $this[] = new Tag('link', $attr);
   }
   
   /**
    * Add a CSS Stylesheet to the document head
    * 
    * @param string $href
    * @param string $media
    */
   public function stylesheet($href, $media = null) {
      $attribs = [];
      if($media) $attribs['media'] = $media;
      $attribs['type'] = 'text/css';

      $this->link($href, 'stylesheet', $attribs);
   }
  
   
   /**
    * Add CSS styles
    * 
    * @param string $selector
    * @param array $styles
    * @param string $media
    */
   public function style($selector, array $styles, $media = null) {
      $attr = ['type' => 'text/css'];
      if($media) $attr['media'] = $media;
      $cssattr = $this->cssAttributeString($styles);
      $css = "{$selector} { {$cssattr} }";
      $this[] = new Element('style', $css, $attr);
   }
   
   /**
    * Add javascript file or code
    * 
    * @param string $src
    * @param array $attribs
    */
   public function script($src = null, $code = null, array $attribs = null) {
      if($attribs === null) $attribs = [];
      if($src) $attribs['src'] = $src;
      $attribs['type'] = 'text/javascript';
      
      $this[] = new Element('script', $code, $attribs);
   }
   
   
   public function cssAttributeString(array $styles) {
      $css = '';
      foreach($styles as $key => $val) {
         $css .= "{$key} : {$val};";
      }
      
      return $css;
   }
}