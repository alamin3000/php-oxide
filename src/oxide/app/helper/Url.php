<?php
namespace oxide\app\helper;
use oxide\http\Request;
use oxide\http\Route;

/**
 * 
 */
class Url {
   protected 
      $_route = null,
      $_request = null;
   
   /**
    * 
    * @param Request $request
    * @param Route $route
    */
   public function __construct(\oxide\http\Context $context) {
      $this->_request = $context->getRequest();
      $this->_route = $context['route'];
   }
   
   /**
    * 
    * @param string|array $path
    * @param string|array $query
    * @param string $fragment
    * @return string
    */
   public function path($path, $query = null, $fragment = null) {
	   $url = '';
	   
	   if(is_array($path)) {
		   $url = implode('/', $path);
	   } else {
         $url = $path;
      }
	  
	   if($query) {
		   if(is_array($query)) {
			   $query = implode('&', $query);
		   }
         
         $url .= '?'.$query;
	   }
	   
	   if($fragment) {
         $url .= '#'.$fragment;
	   }
      
      if(substr($path, 0, 1) !== '/') { // relative
         return rtrim($this->location(true), '/') . '/' . ltrim($path, '/');
      } else {
         return $this->base($path);
      }
   }
   
   /**
    * Return site url, optionally appending given $append param.
    *
    * Site url includes the schema, domain and port information if any. 
    * @access public
    * @param mixed $append (default: null)
    * @return void
    */
   public function site($append = null) {
	   static $site = null;
	   if($site === null) {
		   $site = $this->_request->getUriComponents(Request::URI_SCHEME);
			$site .= '://';
			$site .= $this->_request->getUriComponents(Request::URI_HOST);
			$port = $this->_request->getUriComponents(Request::URI_PORT);
      
			if($port != '' && $port != '80' && $port != '443') {
				$site .= ':' . $port;
			}
	   }
	   
	   return $site .  $this->base($append);
   }
   
   
   /**
    * Returns the base url. if $append provided, it will be added to the base
    * 
    * Base url does not include domain information. It always start with //
    * @access public
    * @param mixed $append (default: null)
    * @return void
    */
   public function base($append = null) {
	   $base = $this->_request->getBase();
	   $append = ltrim($append, '/');
	   if($append) {
		   if($base == '/') {
			   return $base .  trim($append, '/');
		   } else {
			   return $base . '/' .  trim($append);
		   }
	   } else {
		   return $base;
	   }
   }
   
   
   /**
    * Get the current url location.
    * 
    * @access public
    * @param bool $relative (default: true)
    * @return void
    */
   public function location($relative = true) {
	   return $this->_request->getUrl($relative);
   }
}