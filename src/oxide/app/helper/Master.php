<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\app\helper;
use oxide\base\Container;
use oxide\ui\html\Element;
use oxide\ui\html\Tag;
use oxide\util\ArrayString;

class Master extends Container {     
   use \oxide\base\pattern\PropertyAccessTrait;
   
   /**
    * 
    * @param string $string
    */
   public function title($string = null) {
      if($string) $this->title = new Element('title', $string);
      else return $this->title;
   }
   
   /**
    * 
    * @param type $name
    * @param type $content
    * @param type $nameKey
    * @param type $contentKey
    */
   public function meta($name = null, $content = null, $nameKey = 'name', $contentKey = 'content') {
      if(!isset($this->metas)) {
         $this->metas = new ArrayString();
      }
      
      if($name) {
         $this->metas[] = new Tag('meta', [$nameKey => $name, $contentKey => $content]);
      } else {
         return $this->metas;
      }
   }
   
   /**
    * 
    * @param type $href
    * @param type $rel
    * @param array $attr
    */
   public function link($href = null, $rel = null, array $attr = null) {
      if(!(isset($this->links))) {
         $this->links = new ArrayString();
      }
      
      if($href) {
         if(!$attr) $attr = [];
         $attr['href'] = $href;
         $attr['rel'] = $rel;
         $this->links[] = new Tag('link', $attr);
      } else {
         return $this->links;
      }
   }
   
   /**
    * Add a CSS Stylesheet to the document head
    * 
    * @param string $href
    * @param string $media
    */
   public function stylesheet($href = null, $media = null) {
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
   public function style($selector = null, array $styles = null, $media = null) {
      if(!isset($this->styles)) {
         $this->styles = new ArrayString();
      }
      
      if($selector) {
         $attr = ['type' => 'text/css'];
         if($media) $attr['media'] = $media;
         $cssattr = $this->cssAttributeString($styles);
         $css = "{$selector} { {$cssattr} }";
         $this->styles[] = new Element('style', $css, $attr);
      } else {
         return $this->styles;
      }
   }
   
   /**
    * Add javascript file or code
    * 
    * @param string $src
    * @param array $attribs
    */
   public function script($src = null, $code = null, array $attribs = null) {
      if(!isset($this->scripts)) {
         $this->scripts = new ArrayString();
      }
      
      if($src || $code) {
         if($attribs === null) $attribs = [];
         if($src) $attribs['src'] = $src;
         $attribs['type'] = 'text/javascript';

         $this->scripts[] = new Element('script', $code, $attribs);
      } else {
         return $this->scripts;
      }
   }
   
      
   public function cssAttributeString(array $styles) {
      $css = '';
      foreach($styles as $key => $val) {
         $css .= "{$key} : {$val};";
      }
      
      return $css;
   }
   
   public function breadcrumb($title = null, $url = null) {
      if(!isset($this->breadcrumbs)) {
         $this->breadcrumbs = new ArrayString();
      }
      
      if($title) {
         $this->breadcrumbs[$title] = $url;
      } else {
         return $this->breadcrumbs;
      }
   }
   
   public function actions($title = null, $url = null) {
      if(!isset($this->actions)) {
         $this->actions = new ArrayString();
      }
      
      if($title) {
         $this->actions[$title] = $url;
      } else {
         return $this->actions;
      }
   }
   
   public function navigations($title = null, $url = null) {
      if(!isset($this->navigations)) {
         $this->navigations = new ArrayString();
      }
      
      if($title) {
         $this->navigations[$title] = $url;
      } else {
         return $this->navigations;
      }
   }
}