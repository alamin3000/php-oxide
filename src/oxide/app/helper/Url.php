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
      $this->_request = $context->get('request');
      $this->_route = $context->get('route');
   }
   
   public function build($path, $query = null, $fragment = null) {
	   $url = '';
	   
	   if(is_array($path)) {
		   $path = implode('/', $path);
	   }
	  
	   if($query) {
		   if(is_array($query)) {
			   $query = implode('&', $query);
		   }
	   }
	   
	   if($fragment) {
		   if(is_array($fragment)) {
//			   $f
		   }
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
			$site .= $this->_request->getUriComponents(Request::URI_HOST);;
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
	   
	   if($append) {
		   if($base == '/') {
			   return $base .  trim($append);
		   } else {
			   return $base . '/' .  trim($append);
		   }
	   } else {
		   return $base;
	   }
   }
   
   public function location($relative = true) {
	   return $this->_request->getUrl($relative);
   }
}