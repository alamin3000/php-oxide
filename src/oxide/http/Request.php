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
 */
class Request {
   use \oxide\util\pattern\DefaultInstanceTrait;
   
   protected
		$_cookie_identifier = "__REQUEST_ID__",
      $_uriComponents = array(),
      $_routeComponents = array(),
      $_pathParams = array(),
      $_pathParamsRaw = "",
      $_method = null,
      $_vars = array();

   const
      URI_SECURED = 'secured',
      URI_SCHEMA  = 'schema',
      URI_HOST    = 'host',
      URI_URI     = 'uri',
      URI_PATH    = 'path',
      URI_BASE    = 'base',
      URI_SCRIPT  = 'script',
      URI_PORT    = 'port',
      URI_QUERY   = 'query';
   
	/**
	 * constructor
	 *
	 * initializes Request object.  Parses the request URI into sevaral components
	 * heavily replies on $_SERVER variable.
	 * 
	 * @access private
	 */
	private function __construct() {
      $uriComponents = array(
         self::URI_BASE   => dirname(filter_input(INPUT_SERVER, 'SCRIPT_NAME')),
         self::URI_HOST   => filter_input(INPUT_SERVER, 'SERVER_NAME'),
         self::URI_PATH   => rtrim(substr(filter_input(INPUT_SERVER, 'PHP_SELF'), strlen(filter_input(INPUT_SERVER, 'SCRIPT_NAME'))), '/'),
         self::URI_PORT   => filter_input(INPUT_SERVER, 'SERVER_PORT'),
         self::URI_SCRIPT => basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME')),
         self::URI_URI    => filter_input(INPUT_SERVER, 'REQUEST_URI'),
         self::URI_QUERY  => filter_input(INPUT_SERVER, 'QUERY_STRING')
         );

      $ssl = false;
      $https = filter_input(INPUT_SERVER, 'HTTPS');
      
      if($https && $https == 1) /* Apache */ {
         $ssl = true;
      } elseif ($https && $https == 'on') /* IIS */ {
         $ssl = true;
      } elseif (filter_input(INPUT_SERVER, 'SERVER_PORT') == 443) /* others */ {
         $ssl = true;
      }

      if($ssl) {
         $uriComponents[self::URI_SECURED] = true;
         $uriComponents[self::URI_SCHEMA] = 'https';
      } else {
         $uriComponents[self::URI_SECURED] = false;
         $uriComponents[self::URI_SCHEMA] = 'http';
      }

      $path = trim($uriComponents[self::URI_PATH], '/');
      $params = explode('/', $path);
         

      $this->_uriComponents = $uriComponents;
      $this->_pathParams = $params;
      $this->_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
	}
   
   /**
    * Get the HTTP method
    * @return string
    */
   public function getMethod() {
      return $this->_method;
   }
   
   /**
    * Get Path string
    * @return string
    */
   public function getPath() {
      return $this->getUriComponents('path');
   }
   
   /**
    * Get HTTP Request Headers
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function getHeaders($key = null, $default = null) {
      if($this->_headers === null) {
         $this->_headers = getallheaders();
      }

      if($key)
         return (isset($this->_headers[$key])) ? $this->_headers[$key] : $default;
      else
         return $this->_headers;
   }
   
	/**
	 * check if request is duplicate
	 *
	 * Simply compares hash values of $_GET & $_POST array against session key
	 * @return bool
	 */
	public function isDuplicateRequest()
	{
		$result = false;
		$session = Session::getInstance();
		$key = $this->_cookie_identifier;
		$hash = md5(serialize($_POST) . serialize($_GET));
		if($session->read($key)) {
			// check if values are identical
			$cache = $session->read($key);
			
			if($cache == $hash) {
				$result = true;
			}
		}

		$session->write($key, $hash);
		return $result;
	}
	
	/**
	 * Returns given value for requested $key from Uri component array.
	 * 
	 * @access public
	 * @param string $key
	 * @return string|null
	 **/
	public function getUriComponents($key = null) {
      if($key === null) return $this->_uriComponents;
      
		if(!isset($this->_uriComponents[$key])) {
			return null;
		}
		
		return $this->_uriComponents[$key];
	}

	/**
	 * Returns server url including host, schema port
	 * 
    * NOTE: this will only return the server portion, without the path and query
	 * @return string
	 */
   public function getServerUrl() {
      $server = $this->_uriComponents[self::URI_SCHEMA];
      $server .= '://';
      $server .= $this->_uriComponents[self::URI_HOST];

		// if server has irregular port,
		// we need to include it as well
		$port = $this->_uriComponents[self::URI_PORT];
		if($port != '' && $port != '80' && $port != '443') {
			$server .= ':' . $this->_uriComponents[self::URI_PORT];
		}
      //$server .= $this->_uriComponents[self::URI_PATH];
      
      return $server;
   }

	/**
	 * Get absolute URL including server and path information
	 * 
    * Please note, this will NOT include query string information
	 * @return string
	 */
	public function getAbsoluteUrl()
	{
		$server = $this->getServerUrl();
		$path = $this->getUriComponents(self::URI_PATH);

		return "{$server}{$path}";
	}
	
	/**
	 * get a path from given index
	 *
	 * path is complete string after the domain name, ex domain.com/path/string
	 * So path string is /path/string
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
    * Get input value from given $type
    * 
    * Simply used filter_input method
    * @param type $type [INPUT_GET | INPUT_POST | INPUT_SERVER, etc]
    * @param string $key
    * @param mixed $default
    * @param type $filter
    * @param mixed $opitons
    * @return type
    */
   public function getInput($type, $key = null, $default = null, $filter = FILTER_UNSAFE_RAW , $opitons = null) {
		if($key === null) {
			$vals = filter_input_array($type);
         if(!$vals) {
            return array();
         } else {
            return $vals;
         }
		}
      
      $val = filter_input($type, $key, $filter, $opitons);
      
      if($val === FALSE) {
         // an error occured with filter processing
         // we will just return the default value
         return $default;
      }
      
      if($val === NULL) {
         // value is not set
         // we will send the default
         return $default;
      }
      
      return $val;
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
      return $this->getInput(INPUT_POST, $key, $default);
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
      return $this->getInput(INPUT_GET, $key, $default);
	}
	
	/**
	 * return a value from the cookie
	 *
	 * returns raw value without any kind of filtering applied
	 * @access public
	 * @param string $key
	 * @param mixed $default[ value to return if $key not found
	 * @return string
	 */
	public static function getCookie($key, $default = null) {
      return $this->getInput(INPUT_COOKIE, $key, $default);
	}
	
	/**
	 * sets a cookie value back to 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public static function setCookie($key, $value, $exp = null) {
      if(!$exp) {
         $exp = time() * 60 * 60 * 24 * 30;
      }
		setcookie($key, $value, $exp);
	}
   
   /**
    * returns the current full url including path and query string
    * 
    * if $relative is true then the url will be relative, else it will include full
    * server address
    * @param boolean $relative
    */
   public function getCurrentUrl($relative = true, $include_query = true)
   {
      $path = $this->getUriComponents(self::URI_PATH); 
      $query = '';
      if($include_query) {
         $query = $this->getQueryString();
         if($query) { $query = '?' . $query; }
      }
      if($relative) { $server = ''; }
      else { $server = $this->getServerUrl (); }
      
      return sprintf('%s%s%s', $server, $path, $query);
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
		
		// rebuilding the query string and returning it.
		$qstring = '';
		foreach($qparams as $key => $value) {
			$qstring .= "{$key}={$value}&";
		}

		return rtrim($qstring, '&');
	}
	
	/**
	 * convinent get method that checks for both get and post array
	 * 
	 * key will be scanned through $_GET, $_POST in order.
	 * @return 
	 * @param object $key
	 * @param object $default value to return if not found
	 */	
	public function get($key, $default = null) {
      $val = $this->getPost($key, NULL);
      if($val === NULL) {
         $val = $this->getQuery($key, NULL);
      }
      
      if($val === NULL) {
         return $default;
      } else {
         return $val;
      }
	}
	
	/**
	 * get client's browse info object
	 * 
	 * simply convinent function wraps get_browser method
	 * @return 
	 * @param object $agent
	 */
	public static function getBrowserObject($agent = null) {
		return get_browser($agent, false);
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