<?php
namespace oxide\helper;
use oxide\application\View;
use oxide\helper\_html;

abstract class _template {
   public static
      $title = null,
      $content = null,
      $links = [],
      $scripts = [],
      $snippets = [],
      $styles = [];
      
   public static function initialize($args = null) {
      
   }
   
   public static function scripts($src = null, $identifier = null) {
      if($src == null) {
         $buffer = '';
         foreach(self::$scripts as $script) {
            $buffer.= _html::tag('script', null, ['src' => $script, 'type' => 'text/javascript']);
         }
         return $buffer;
      }
      
      if($identifier) {
         self::$scripts[$identifier][] = $src;
      } else {
         self::$scripts[] = $src;
      }
   }
   
   public static function snippets($code = null, $identifier = null) {
      if($code === null) {
         $buffer = '';
         foreach (self::$snippets as $snippet) {
            $buffer.= _html::tag('script', $snippet, ['type' => 'text/javascript']);
         }
         return $buffer;
      }
      
      if($identifier) {
         self::$snippets[$identifier][] = $identifier;
      } else {
         self::$snippets[] = $code;
      }
   }
   
   /**
    * 
    * @param array $styles
    * @param type $media
    * @return type
    */
   public static function styles($selector = null, array $attributes = null,  $media = null) {
      if($selector == null) {
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

         return _html::end();
      }
      
      $styles = [$selector => $attributes];
      if($media)  self::$styles[$media] = $styles;
      else self::$styles[] = $styles;
   }
   
   /**
    * Set the page title
    * This will be used for HTML title tag
    * @staticvar type $title
    * @param type $str
    * @return type
    */
   public static function title($str = FALSE) {
      if($str === FALSE) return self::$title;
      else self::$title = $str;
   }
   
   /**
    * Get/Set current main view content
    * @return oxide\application\View
    */
   public static function content($view = FALSE) {
      if($view === FALSE) return self::$content;
		else self::$content = $view;
   }   
   
   /**
    * 
    * @staticvar type $links
    * @param type $item
    * @param type $link
    */
   public static function navigations($item = null, $link = null) {
      return self::links('navigations', $item, $link);
   }
   
   public static function actions($item = null, $link = null) {
      return self::links('actions', $item, $link);
   }
   
   public static function links($offset, $item = null, $link = null) {
      if(self::$links === null) {
         self::$links = [];
      }
      
      if(!isset(self::$links[$offset])) {
         self::$links[$offset] = [];
      }
      
      $links = &self::$links[$offset];
      if(!$item) return $links;
      else $links[$item] = $link;
   }
   
   public static function breadcrumbs($item = null, $link = null) {
      if(!isset(self::$links['breadcrumbs'])) {
         self::$links['breadcrumbs'] = ['Home' => '/'];
      }
      return self::links('breadcrumbs', $item, $link);
   }
   
   /**
    * Set global view/template variable
    * Uses View::share
    * @see View::share
    * @param type $key
    * @param type $value
    */
   public static function set($key, $value = null) {
      View::share($key, $value);
   }
   
   /**
    * Get globally shared data using set or View::share
    * @param type $key
    * @param type $default
    * @return type
    */
   public static function get($key, $default = null) {
      return View::shared($key, $default);
   }
  
}