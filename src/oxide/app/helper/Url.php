<?php
namespace oxide\app\helper;
use oxide\http\Request;

/**
 * 
 */
class Url {	
   protected 
      $_route = null,
      $_request = null;
   
   public function __construct(HelperContainer $container) {
      $context = $container->getContext();
      $this->_request = $context->getRequest();
      $this->_route = $context->getRoute();
   }
   
   /**
    * Get the host name.  This will include subdomin
    * 
    * @return string
    */
   public function host() {
      return $this->_request->getUriComponents(Request::URI_HOST);
   }
   
   /**
    * Get the domain name
    * 
    * @return string
    */
   public function domain() {
      return $this->host();
   }
   
   /**
    * scheme/protocol for the site (http/https)
    * 
    * @return string
    */
   public function schema() {
      return $this->_request->getUriComponents(Request::URI_SCHEME);
   }
   
   /**
    * port number
    * 
    * @return int
    */
   public function port() {
      return $this->_request->getUriComponents(Request::URI_PORT);
   }
   
   /**
    * Full server url (http://domain.com)
    * 
    * @return string
    */
   public function serverUrl() {
      $server = $this->schema();
      $server .= '://';
      $server .= $this->host();
      $port = $this->port();
      
		if($port != '' && $port != '80' && $port != '443') {
			$server .= ':' . $port;
		}
      
      return $server;
   }
   
   
   
   /**
    * Get GET query params
    * 
    * @param string|null $key
    * @param mixed $default
    * @return string|null|array
    */
   public function query($key = null, $default = null) {
      return $this->_request->getQuery($key, $default);
   }
   
   /**
    * Get post param(s)
    * 
    * @param string|null $key
    * @param mixed $default
    * @return string|null|array
    */
   public function post($key = null, $default = null) {
      return $this->_request->getPost($key, $default);
   }
   
   /**
    * Get the base url for the current requested site
    * 
    * @param bool $relative
    * @return string
    */
   public function base($relative = true) {
      $request = $this->_request;
      $base = $request->getUriComponents('base');
      if(!$relative) {
         $server = $this->serverUrl();
         $base = $server . '/' . trim($base, '/\\');
      }
      
      return $base;
   }
   
   /**
    * Get relative path from the given 
    * 
    * Taken from 
    * @param type $from
    * @param type $to
    */
   public function relative($from, $to) {
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
    * @param bool $relative
    * @param mixed $addQueryKeyValue
    * @param mixed $removeQueryKey
    * @return string
    */
	public function url($relative = true, array $addQueryKeyValue = null, $removeQueryKey = null, $prepareForQuery = true) {
      $request = $this->_request;
		$path = $this->pathUrl($relative);
		$query = $request->getQueryString($addQueryKeyValue, $removeQueryKey);

		if($query) {
			$path = "{$path}?{$query}";
         if($prepareForQuery) $path .= '&';
		} else {
			$path = "$path";
         if($prepareForQuery) $path .= '?';
		}
      
      return $path;
	}
   
   /**
    * 
    * @param type $relative
    * @return string
    */
   public function pathUrl($relative = true) {
      $request = $this->_request;
      $path = '/' . trim($request->getUriComponents('path'), '/');
      
      if(!$relative) {
         $path = $this->serverUrl() .  $path;
      }
      
      return $path;
   }
   
   /**
    * 
    * @return \oxide\http\Route
    */
   public function route() {
      return $this->_route;
   }


   /**
    * Returns current routed module name
    * 
    * @return string
    */
   public function module() {
      return $this->_route->module;
   }
   
   /**
    * Returns current routed controller name
    * 
    * @return string
    */
   public function controller() {
      return $this->_route->controller;
   }
   
   /**
    * Returns current routed action name
    * Uses route object for information
    * @return string
    */
   public function action() {
      return $this->_route->action;
   }
   
   /**
    * 
    * @param type $index
    * @return type
    */
   public function param($index = null) {
      if($index == null) {
         return $this->_route->params;
      } else {
         return $this->_route->params[$index];
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
   public function path($index = null, $default = null) {
      $this->_request->getPaths($index, $default);
   }
   
   /**
    * returns only the module portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module
    * @return string
    */
   public function modulePath() {
		return "/{$this->_route->module}";
   }

   /**
    * returns only the module/controller portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module/controller
    * @return string
    */
   public function controllerPath() {
		return "/{$this->_route->module}/{$this->_route->controller}";
   }

   /**
    * returns only the module/controller/action portion of the url
    *
    * Ex: http://domain.com/module/controller/action?var1=3
    * will return /module/controller/action
    * @return string
    */
	public function actionPath() {
		return "/{$this->_route->module}/{$this->_route->controller}/{$this->_route->action}";
	}
   
//   public function build($paths)
}