<?php
namespace oxide\helper;
use oxide\application\View;

abstract class _template {
   public static function initialize($args = null) {
      
   }
   
   /**
    * Set the page title
    * This will be used for HTML title tag
    * @staticvar type $title
    * @param type $str
    * @return type
    */
   public static function title($str = FALSE) {
      static $title = null;
      if($str === FALSE) return $title;
      else $title = $str;
   }
   
   /**
    * Get/Set current main view content
    * @return oxide\application\View
    */
   public static function content($view = FALSE) {
      static $contentview = null;
      if($view === FALSE) return $contentview;
		else $contentview = $view;
   }   
   
   /**
    * 
    * @staticvar type $links
    * @param type $item
    * @param type $link
    */
   public static function navigations($item = null, $link = null) {
      static $links = null;
      if($links === null)  $links = [];
      if(!$item) return $links;
      else $links[$item] = $link;
   }
   
   public static function actions($item = null, $link = null) {
      static $links = null;
      if($links === null)  $links = [];
      if(!$item) return $links;
      else $links[$item] = $link;
   }
   
   public static function links($item = null, $link = null) {
      static $links = null;
      if($links === null)  $links = [];
      if(!$item) return $links;
      else $links[$item] = $link;
   }
   
   public static function breadcrumb($item = null, $link = null) {
      static $links = null;
      if($links === null)  $links = [];
      if(!$item) return $links;
      else $links[$item] = $link;
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