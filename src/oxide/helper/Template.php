<?php
namespace oxide\helper;
use oxide\ui\html\Element;
use oxide\application\View;
use oxide\util\ArrayString;

/**
 * All template related contents
 */
abstract class Template {
   private static 
           $_title = null,
           $_links = array();
   
   const 
      CONTENT_VIEW   = 'content',
      LINK_TYPE_BREADCRUMB = 'template-link-type-breadcrumb',
      NAVIGATION_KEY = 'template-navigation-key',
      ACTION_KEY     = 'template-action-key',
      DEFAULT_KEY    = 'template-defalt-key';

   
   /**
    * Manages various links for the layout/template
    * @return ArrayString Description
    */
   public static function links($item = null, $link = null, $offset = null) {
      if($offset == null) { $offset = self::DEFAULT_KEY; }
      
      if(!isset(self::$_links[$offset])) {
         $element = new ArrayString();
         $element->registerStringifyCallback(function(ArrayString $string) {
            if(count($string))
               return _html::ul($string->toArray(), null, _html::LIST_SMART_LINK);  
            else return '';
         });
         self::$_links[$offset] = $element;
      }

      if($item !== null) {
         if($link !== null)
            self::$_links[$offset][$item] =$link;
         else 
            self::$_links[$offset][] = $item;
      } else {
         return self::$_links[$offset];
      }
   }
   
   /**
    * holds main page naviagation links
    * @return ArrayString
    */
   public static function navigations($item = null, $link = null) {
      return self::links($item, $link, self::NAVIGATION_KEY);
   }

   /**
    * main web actions.
    * 
    * These are the links that usually show by the side of contents.
    * @param type $item
    * @param type $link
    * @return type
    */
   public static function actions($item = null, $link = null) {
      return self::links($item, $link, self::ACTION_KEY);
   }
   
   /**
    * returns the main view
    * @return oxide\application\View
    */
   public static function content($view = null) {
      static $contentview = null;
		if($view) {
			$contentview = $view;
		} else {
			return $contentview;
		}
   }
   

   /**
    * @return ArrayString Description
    */
   public static function breadcrumbs($item = null, $link = null) {
      if(!isset(self::$_links[self::LINK_TYPE_BREADCRUMB])) {
         self::links('home', url::base(), self::LINK_TYPE_BREADCRUMB);
      }
      
      return self::links($item, $link, self::LINK_TYPE_BREADCRUMB);
   }

   /**
    * 
    * @param type $title
    * @return type   
    */
   public static function title($title = null) {
      if($title) {
         self::$_title = $title; // setting the original tittle
         html::title($title);
      }
      
      else { return self::$_title; }
   }
   
   /**
    * Subtitle of the page
    * 
    * @staticvar null $subtitle
    * @param type $text
    * @return null
    */
   public static function subtitle($text = null) {
      static $subtitle = null;
      if($text) {
         $subtitle = $subtitle;
      }
      
      return $subtitle;
   }
   
   public static function moduleCssClass()
   {
      
   }
   
   /**
    * Set 
    * @param type $key
    * @param type $value
    */
   public static function set($key, $value = null) {
      View::share($key, $value);
   }
   
   /**
    * 
    * @param type $key
    * @param type $default
    * @return type
    */
   public static function get($key, $default = null) {
      return View::shared($key, $default);
   }
}