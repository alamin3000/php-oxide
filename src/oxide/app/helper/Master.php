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

class Master extends Container {     
   public
      /**
       * @var string Head title
       */
      $title = null,
      
      /**
	    * Stores meta information using Html tag tuple ie [$tagname, $content, $attributes, $void]
	    *
       * @var array meta tags using 3 or 4 tuples
       */
      $metas = [],
      
      $stylesheets = [],
      $styles = [],
      $links = [],
      $scripts = [],
      $breadcrumbs = [],
      $actions = [],
      $navigations = [];
   
   protected
      $_html = null;
   
   public function __construct(HelperContainer $container) {
      parent::__construct();
      $this->_html = $container->get('html');
   }
   
   
   /**
    * <TITLE> string for the webpage
    * 
    * @param string $string
    */
   public function title($string = null) {
      if($string) $this->title = $string;
      else {
         return $this->_html->tag('title', $this->title);
      }
   }
   
   /**
    * Add <META> Tags
    * 
    * @param type $name
    * @param type $content
    * @param type $nameKey
    * @param type $contentKey
    */
   public function metas($name = null, $content = null, $nameKey = 'name', $contentKey = 'content') {
      if($name) {
         $this->metas[] = ['meta', null, [$nameKey => $name, $contentKey => $content]];
      } else {
         return $this->_html->tags($this->metas);
      }
   }
   
   /**
    * LINK tag for the html head.  
    * 
    * Usually used for adding stylesheets.
    * @param type $href
    * @param type $rel
    * @param array $attr
    */
   public function links($href = null, $rel = null, array $attr = null) {
      if($href) {
         if(!$attr) $attr = [];
         $attr['href'] = $href;
         $attr['rel'] = $rel;
         $this->links[] = ['link', null, $attr];
      } else {
         return $this->_html->tags($this->links);
      }
   }
   
   /**
    * Add a CSS Stylesheet to the document head
    * 
    * @param string $href
    * @param string $media
    */
   public function stylesheets($href = null, $media = null) {
      if($href) {
         $attribs = [];
         if($media) $attribs['media'] = $media;
         $attribs['type'] = 'text/css';
         $attribs['href'] = $href;
         $attribs['rel'] = 'stylesheet';
         $this->stylesheets[] = ['link', null, $attribs];
      } else {
         return $this->_html->tags($this->stylesheets);
      }
   }
  
   
   /**
    * Add CSS styles
    * 
    * @param string $selector
    * @param array $styles
    * @param string $media
    */
   public function styles($selector = null, array $styles = null, $media = null) {
      if($selector) {
         $attr = ['type' => 'text/css'];
         if($media) $attr['media'] = $media;
         $cssattr = $this->cssAttributeString($styles);
         $css = "{$selector} { {$cssattr} }";
         $this->styles[] = ['style', $css, $attr];
      } else {
         return $this->_html->tags($this->styles);
      }
   }
   
   /**
    * Add javascript file or code
    * 
    * @param string $src
    * @param array $attribs
    */
   public function scripts($src = null, $code = null, array $attribs = null) {
      if($src || $code) {
         if($attribs === null) $attribs = [];
         if($src) $attribs['src'] = $src;
         $attribs['type'] = 'text/javascript';

         $this->scripts[] = ['script', $code, $attribs];
      } else {
         return $this->_html->tags($this->scripts);
      }
   }
   
    
   /**
    * cssAttributeString function.
    * 
    * @access public
    * @param array $styles
    * @return void
    */
   public function cssAttributeString(array $styles) {
      $css = '';
      foreach($styles as $key => $val) {
         $css .= "{$key} : {$val};";
      }
      
      return $css;
   }
   
   /**
    * Add/set breadcrumb
    * 
    * @param null|string|array $title
    * @param null|string $url
    * @return string
    */
   public function breadcrumbs($title = null, $url = null) {
      if($title) {
         if(is_array($title)) {
            $this->breadcrumbs = $title;
         } else {
            $this->breadcrumbs[$title] = $url;
         }
      } else {
         return $this->_html->ul($this->breadcrumbs);
      }
   }
   
   /**
    * Add/set actions
    * 
    * @param null|string|array $title
    * @param null|string $url
    * @return string
    */
   public function actions($title = null, $url = null) {
      if($title) {
         if(is_array($title)) {
            $this->actions = $title;
         } else {
            $this->actions[$title] = $url;
         }
      } else {
         return $this->_html->ul($this->actions);
      }
   }
   
   /**
    * 
    * @param type $title
    * @param type $url
    * @return type
    */
   public function navigations($title = null, $url = null) {
      if($title) {
         $this->navigations[$title] = $url;
      } else {
         return $this->_html->ul($this->navigations);
      }
   }
}