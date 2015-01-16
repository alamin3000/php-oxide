<?php
namespace oxide\http;

/**
 * Http Request object
 *
 * Wraps incoming http request information
 * This object is not meant for general Request object for communicating with oter server/services
 * In fact, you can't create a new Request object.  You must use static method getCurrentRequest()
 * to obtain current HTTP request
 * 
 * @package oxide
 * @subpackage http
 * @todo change _routeComponents to _moduleCompnonent
 * @todo better path components
 * @todo utilize base
 */
class Request {   
   protected 
      /**
       * @var string Holds the current request url
       */
      $_url = null,
      $_base = '',
           
      $_relativeUrl = null,
           
      /**
       * @var boolean Indicates if the request is secured or not
       */
      $_secured = false;


   protected
		$_cookie_identifier = "__REQUEST_ID__",
      $_uriComponents = array(),
      $_routeComponents = array(),
      $_pathParams = array(),
      $_method = null,
      $_queries = [],
      $_posts = [],
      $_vars = [];

   const
      URI_SCHEMA  = 'schema',
      URI_HOST    = 'host',
      URI_PATH    = 'path',
      URI_BASE    = 'base',
      URI_SCRIPT  = 'script',
      URI_PORT    = 'port',
      URI_PASS    = 'pass',
      URI_USER    = 'user',
      URI_FRAGMENT = 'fragment',
      URI_QUERY   = 'query';
   
	/**
	 * constructor
	 *
	 * Cannot instanciate. Must use create methods instead.
	 * @access private
	 */
	private function __construct() {
      
	}
   
   /**
    * Create a request from given $url
    * 
    * @param string $url
    * @return \self
    */
   public static function createFromUrl($url) {
      $request = new self();
      $uris = parse_url($url);
      $request->_url = $url;
      $request->_uriComponents = $uris;
      
      if(isset($uris[self::URI_PATH])) {
         $params = array_filter(explode('/', $uris[self::URI_PATH]));
         $request->_pathParams = array_values($params);
      }
      
      if(isset($uris[self::URI_SCHEMA])) {
         if(strtolower($uris[self::URI_SCHEMA]) == 'https') {
            $request->_secured = true;
         } else {
            $request->_secured = false;
         }
      } else { // schema is not found
         $uris[self::URI_SCHEMA] = 'http'; // set default schema
      }
          
      
      if(isset($uris[self::URI_QUERY])) {
         $query = $uris[self::URI_QUERY];
         parse_str($query, $request->_queries);
      }
      
      $relatives = [$uris[self::URI_PATH]];
      if(isset($uris[self::URI_QUERY])) $relatives[] = '?' . $uris[self::URI_QUERY];
      if(isset($uris[self::URI_FRAGMENT])) $relatives[] = '#' . $uris[self::URI_FRAGMENT];
      $request->_relativeUrl = implode('', $relatives);
      return $request;
   }
   
   /**
    * get the current server request
    * 
    * @return \self
    */
   public static function currentServerRequest() {
      static $instance = null;
      if($instance === null) {
         $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
         $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
         $schema = null;
         $https = filter_input(INPUT_SERVER, 'HTTPS');
         if($https && $https == 1) /* Apache */ {
            $schema = 'https';
         } elseif ($https && $https == 'on') /* IIS */ {
            $schema = 'https';
         } elseif (filter_input(INPUT_SERVER, 'SERVER_PORT') == 443) /* others */ {
            $schema = 'https';
         } else {
            $schema = 'http';
         }

         $url = "{$schema}://{$host}{$uri}";

         // create request from the url and setup the additional information
         $instance = self::createFromUrl($url);
         $instance->_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
         $instance->_posts = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
         $instance->_headers = getallheaders();
      }
      
      return $instance;
   }
   
   /**
    * 
    * @return bool
    */
   public function isSecured() {
      return $this->_secured;
   }
   
   /**
    * Get the HTTP method
    * 
    * @return null|string
    */
   public function getMethod() {
      return $this->_method;
   }
   
   /**
    * Gets the full URL for the request.
    * This may or may not have schema and host information, depending on whether
    * those information was provided when creating the request or not.
    * @return string
    */
   public function getUrl($relative = true) {
      if($relative) return $this->_relativeUrl;
      else return $this->_url;
   }
   
   /**
    * Get HTTP Request Headers
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function getHeaders($key = null, $default = null) {
      if(!$key) return $this->_headers;
      if($key)
         return (isset($this->_headers[$key])) ? $this->_headers[$key] : $default;
      else
         return $this->_headers;
   }
	
	/**
	 * Returns given value for requested $key from Uri component array.
	 * 
	 * @access public
	 * @param string $key
	 * @return string|null
	 **/
	public function getUriComponents($key = null, $default = null) {
      if($key === null) return $this->_uriComponents;
		if(!isset($this->_uriComponents[$key])) {
			return $default;
		}
		
		return $this->_uriComponents[$key];
	}

	
	/**
	 * get a path from given index
	 *
    * Example
	 * from given path  /path/string
	 * Accessing to index 1 in the above example will return 'string'
	 * 
	 * @access public
	 * @param int $index
	 * @param mixed $default value to return if given index is not found
	 * @return string
	 */
	public function getPaths($index = null, $default = '') {
      if($index === null) return $this->_pathParams;
		if(isset($this->_pathParams[$index])) {
			return $this->_pathParams[$index];
		} else {
			return $default;
		}
	}

   /**
	 * gets a value post value
	 *
	 * returns raw value without applying any kind of filtering.
	 * @access public
	 * @param string $key
	 * @param mixed $default value to return if given $key is not found
	 * @return mixed
	 */
	public function getPost($key = null, $default = null) {
      if($key === null) return $this->_posts;
      if(isset($this->_posts[$key])) return $this->_post[$key];
      return $default;
	}
	
	/**
	 * returns a value from the query string
	 *
	 * returns raw value without any kind of filtering applied.
	 * @access public
	 * @param string $key
	 * @param mixed $default value to return if $key not found
	 * @return string
	 */
	public function getQuery($key = null, $default = null) {
      if($key === null) return $this->_queries;
      if(isset($this->_queries[$key])) return $this->_queries[$key];
      return $default;
	}
   
   public function setParams(array $params) {
      $this->_vars = $params;
   }
   
   /**
    * Get
    * @param type $index
    * @param type $default
    * @return type
    */
   public function getParam($index = null, $default = null) {
      if($index === null) return $this->_vars;
      if(isset($this->_vars[$index])) return $this->_vars[$index];
      return $default;
   }
   
   /**
    * Get input value from given $type
    * 
    * This will always return from user Input
    * Simply used filter_input method
    * @param type $type [INPUT_GET | INPUT_POST | INPUT_SERVER, etc]
    * @param string $key
    * @param mixed $default
    * @param type $filter
    * @param mixed $opitons
    * @return type
    */
   public static function getInput($type, $key = null, $default = null, 
           $filter = FILTER_UNSAFE_RAW , $opitons = null) {
		if($key === null) {
			$vals = filter_input_array($type);
         if(!$vals) {
            return [];
         } else {
            return $vals;
         }
		}
      
      $val = filter_input($type, $key, $filter, $opitons);
      if($val === FALSE || $val === NULL) {
         return $default;
      }
      
      return $val;
   }
	
	/**
	 * get query string
	 *
	 * allows to add and remove from query before retriving the string
	 * @access public
	 * @param mixed $add key value to the query string, if exists, it will be overridden
	 * @param mixed $remove remove the key and it's value from the string
	 * @return string
	 */
	public function getQueryString($add = null, $remove = null) {
		// get the query string array
		$qparams = $this->getQuery();
		
		// add to query string, if requested
		if($add) {
			if(is_array($add)) {
				foreach($add as $key => $value) {
					$qparams[$key] = $value;
				}
			}
		}
		
		// remove from query if requested
		if($remove) {
			if(is_array($remove)) {
				foreach($remove as $key) {
					unset($qparams[$key]);
				}
			} else {
				unset($qparams[$remove]);
			}
		}

		return http_build_query($qparams);
	}
	
	
	/**
	 * get current client IP address
	 *
	 * @return string
	 */
	public static function getIpAddress() {
      $client_ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_UNSAFE_RAW);
		if($client_ip) {
			return $client_ip;
		} 
      
      $forward_ip = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_UNSAFE_RAW);
      if ($forward_ip) {
			return $forward_ip;
		} 
      
      else {
			return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_UNSAFE_RAW);
		}
	}
}