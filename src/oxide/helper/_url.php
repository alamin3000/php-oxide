<?php
namespace oxide\helper;

/**
 * 
 */
abstract class _url
{	
   
   /**
    *
    * @access public
    * @param type $arr 
    */
	public static function generate($arr) {
		
	}


   /**
    *
    * @access public 
    */
	public static function buildQuery()
	{
		
	}

   /**
    * Returns the current server absolute path
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return http://domain.com
    * 
    * @return string
    */
   public static function serverUrl()
   {
		$request = App::context()->getRequest();
      return $request->getAbsoluteServerURL();
   }
   
   /**
    * 
    * @return string
    */
   public static function base()
   {
      $request = App::context()->getRequest();
      return $request->getUriComponents('base');
   }
   
   /**
    * Get relative path from the given 
    * 
    * Taken from 
    * @param type $from
    * @param type $to
    */
   public static function relative($from, $to)
   {
      // some compatibility fixes for Windows paths
      $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
      $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
      $from = str_replace('\\', '/', $from);
      $to   = str_replace('\\', '/', $to);

      $from     = explode('/', $from);
      $to       = explode('/', $to);
      $relPath  = $to;

      foreach($from as $depth => $dir) {
          // find first non-matching dir
          if($dir === $to[$depth]) {
              // ignore this directory
              array_shift($relPath);
          } else {
              // get number of remaining dirs to $from
              $remaining = count($from) - $depth;
              if($remaining > 1) {
                  // add traversals up to first matching dir
                  $padLength = (count($relPath) + $remaining - 1) * -1;
                  $relPath = array_pad($relPath, $padLength, '..');
                  break;
              } else {
                  $relPath[0] = '/' . $relPath[0];
              }
          }
      }
      return implode('/', $relPath);
   }

   /**
    * Returns the current script path including the query string
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module/controller/action?var1=3
    * @param mixed $add
    * @param mixed $remove
    * @return string
    */
	public static function url($add = null, $remove = null)
	{
		$request = App::context()->getRequest();
		$path = trim($request->getUriComponent('path'), '/');
		$query = $request->getQueryString($add, $remove);

		if($query) {
			return "/{$path}?{$query}";
		} else {
			return "/$path";
		}
	}
   
   /**
    * Returns abosolute URL of the correct location
    * 
    * Includes both server and path information.  Does not include Query param
    * @return type
    */
   public static function current()
   {
		$request = App::context()->getRequest();
      $abs = $request->getAbsoluteURL();
      
      return $abs;
   }
   
   /**
    * 
    * @return \oxide\http\Route
    */
   public static function route()
   {
      return App::context()->route;
   }


   /**
    * Returns current routed module name
    * 
    * @return string
    */
   public static function module()
   {
      return self::route()->module;
   }
   
   /**
    * Returns current routed controller name
    * 
    * @return string
    */
   public static function controller()
   {
      return self::route()->controller;
   }
   
   /**
    * Returns current routed action name
    * Uses route object for information
    * @return string
    */
   public static function action()
   {
      return self::route()->action;
   }
   
   /**
    * 
    * @param type $index
    * @return type
    */
   public static function param($index = null)
   {
      if($index == null) {
         return self::route()->params;
      } else {
         return self::route()->params[$index];
      }
   }
   
   /**
    * Returns from the URL path
    * 
    * Path in this context is referred to URL in address bar.
    * This method looks at URL as strings separated by slash '/'
    * 
    * if index is provided, string for that path is returned
    * if index isn't provided, full path is returned as string
    * @param int $index
    * @return string
    */
   public static function path($index = null)
   {
      $request = App::context()->getRequest();
      if($index) 
         return $request->getPathAtIndex($index);
      else 
         return $request->getUriComponent(oxide\http\Request::URI_PATH);
   }
   

   /**
    * returns only the module portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module
    * @return string
    */
   public static function moduleUrl()
   {
      $route = self::route();
		return "/{$route->module}";
   }

   /**
    * returns only the module/controller portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module/controller
    * @return string
    */
   public static function controllerUrl()
   {
      $route = self::route();
		return "/{$route->module}/{$route->controller}";
   }

   /**
    * returns only the module/controller/action portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module/controller/action
    * @return string
    */
	public static function actionUrl()
	{
      $route = self::route();
		return "/{$route->module}/{$route->controller}/{$route->action}";
	}
}