<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui;
use oxide\helper\_html;

class MasterPage extends Page {
   public 
      $contentKey = 'content',
      $stylesheets = [],
      $metas = [],
      $styles = [],
      $snippets = [],
      $scripts = [];
   
   
   /**
    * Add Meta content for the HTML document
    * 
    * @param string $name
    * @param string $content
    * @param string $key_name
    * @param string $key_content
    */
   public function addMeta($name, $content, $key_name = 'name', $key_content = 'content') {
      $this->metas[] = ['meta', null, [$key_name => $name, $key_content => $content]];
   }
   
   /**
    * Render meta tags
    * 
    * @return string
    */
   public function renderMetas() {
      if($this->metas)
         return _html::tags($this->metas);
      else return null;
   }
   
   /**
    * Add a CSS Stylesheet to the document head
    * 
    * @param string $href
    * @param string $media
    */
   public function addStylesheet($href, $media = null) {
      $attribs = [];
		$attribs['href'] = $href;
		$attribs['rel'] = 'stylesheet';
      if($media) $attribs['media'] = $media;

      $this->stylesheets[] = ['link', null, $attribs];
   }
   
   /**
    * Renders stylesheets
    * 
    * @return string
    */
   public function renderStylesheets() {
      if($this->stylesheets)
         return _html::tags($this->stylesheets);
      
      return null;
   }
   
   /**
    * Add CSS styles
    * 
    * @param string $selector
    * @param array $styles
    * @param string $media
    */
   public function addStyles($selector, array $styles, $media = null) {
      $arr = [$selector => $styles];
      if($media)  self::$styles[$media] = $arr;
      else self::$styles[] = $arr;
   }
   
   /**
    * Render CSS styles
    * @return string
    */
   public function renderStyles() {
      $attr = ['type' => 'text/css'];
      _html::start('style', $attr);
      foreach(self::$styles as $media => $styles) {
         if($media) print "@media {$media} {";
         foreach($styles as $selector => $styles) {
            print $selector . "{";
            foreach($styles as $key => $value) {
               print $key . ":" . $value . ";";
            }
            print "}";
         }
         if($media) print "}";
      }

      return _html::end('style');
   }
   
   /**
    * Add javascript file
    * 
    * @param string $src
    * @param array $attribs
    */
   public function addScript($src = null, array $attribs = null) {
      if($attribs === null) {
         $attribs = [];
      }
      
      $attribs['src'] = $src;
      $attribs['type'] = 'text/javascript';
      
      $this->scripts[] = ['script', null, $attribs];
   }
   
   /**
    * Render script file
    * @return string
    */
   public function renderScript() {
      if($this->scripts) {
         return _html::tags($this->scripts);
      }
      
      return null;
   }
   
   /**
    * Add JavaScript code snippets
    * 
    * @param string $code
    * @param array $attribs
    */
   public function addSnippet($code, array $attribs = null) {
      if($attribs === null) {
         $attribs = [];
      }
      
      $attribs['type'] = 'text/javascript';
      $this->snippets[] = ['script', $code, $attribs];
   }
   
   /**
    * Render javascript snippets
    * @return string
    */
   public function renderSnippets() {
      if($this->snippets) {
         return _html::tags($this->snippets);
      }
      return null;
   }
   
   /**
    * Set the title for the html page
    * 
    * @param string $title
    */
   public function setTitle($title) {
      $this->title = $title;
   }
   
   /**
    * Render the title tag
    * @return string
    */
   public function renderTitle() {
      return _html::tag('title', $this->title);
   }
   
   
   
}