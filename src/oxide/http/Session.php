<?php
namespace oxide\http;

/**
 * Session Object.
 *
 * Session object has three main functionalities
 * first, it encapsulates $_SESSION arary and provides object interface for accessing session variables.
 * second, it provides methods to configure various session related settings.
 * third, it implements several security measures.
 *
 * Session object is singleton, can not be explicitly created.  Must use getInstance() instead.
 *
 * @package oxide
 * @subpackage http
 * @author Alamin Ahmed <aahmed753@gmail.com>
 * @todo implement ArrayAccess
 */
class Session { 
   protected
      $_started = false;
   
	protected 
      $_options = [
         'session_id' => null,
         'cookie_path' => '/',
         'cookie_timeout' => 60 * 60 * 6,
         'garbage_timeout' => 60 * 60 * 60 + 600,
         'session_dir' => 'oxide_session',
         'cookie_secure' => false,
         'cookie_domain' => null
      ],
		$_namespace = '',
		$_id = '';
   
	
	/**
	 * constructor
	 *
	 * this object can not be created.  this is a static class.
	 * use getInstance instead
	 * @access private
	 */
	public function __construct($namespace = null, array $options = null) {
      $this->_namespace = $namespace;      
      if($options) {
         $this->_options = array_merge($this->_options, $options);
      }
      
      $this->start();
	}
 
	/**
	 * start the session
	 * @access public
	 * @throw HeaderAlreadySentException
	 * @throw SessionAlreadyStartedException
	 */
	protected function start() {
      $options = $this->_options;
      
      // check if header is already sent.  if so throw an exception.
      $file = null;
      $line = null;
      if(headers_sent($file,$line)) {
        throw new exception\HeaderAlreadySentException();
      }

      // check if session has started automatically.
      if(session_id()) {
        throw new exception\SessionAlreadyStartedException();
      }

      $cookie_path = $options['cookie_path'];
      $cookie_timeout = $options['cookie_timeout'];
      $garbage_timeout = $options['garbage_timeout']; // in seconds
      $domain = $options['cookie_domain'];
      $secured = $options['cookie_secure'];
      session_set_cookie_params($cookie_timeout, $cookie_path, $domain, $secured, TRUE);
      ini_set('session.gc_maxlifetime', $garbage_timeout);
      session_cache_limiter("must-revalidate");

      // starting the sesion.
      session_start(); 
      
      return session_id();
	}
   
   protected function securityCheck() {
      if(isset($_SESSION['REMOTE_ADDR']) && isset($_SESSION['HTTP_USER_AGENT'])) {
         if($_SESSION['HTTP_USER_AGENT'] == $_SERVER['HTTP_USER_AGENT'] &&
            $_SESSION['REMOTE_ADDR'] == $_SERVER['REMOTE_ADDR']) {
            return true;
         }
      }
      
      return false;
   }



   public function end() {
      
   }
		
	/**
	 * get session id
	 * @access public
	 * @return string
	 */
	public function getId() {
      return $this->_id;
	}
	
	/**
	 * sets session id.
	 *
	 * must be call prior to starting the session.
	 * @access public
	 * @param $id string
	 * @throws Oxide_Session_Exception_AlreadyStarted
	 */
	public function setId($id) {
      session_id($id);
	}
	
	/**
	 * regenerate a session id.
	 *
	 * this should be done after session is started.
	 * @access public
	 */
	public function regenerateId($deleteold = true) {
      session_regenerate_id($deleteold);
      $this->_id = session_id();
	}
   
   /**
    * Returns the session namespace.
    * 
    * This name is prepended to every session keys.
    * @return string
    */
   public function getNamespace() {
      return $this->_namespace;
   }
	
	
	/**
	 * reads given key from session
    * 
	 * @access public
	 * @param $key string
	 * @return mixed
	 */
   public function read($key, $default = null) {
		$key = "{$this->_namespace}.{$key}";
		if(isset($_SESSION[$key])) {
			return $_SESSION[$key];
		} else {
			return $default;
		}
   }
   
   /**
    * writes given key and value to session
    * 
    * @access public
    * @param $key string
    * @param $value mixed
    */
   public function write($key, $value) {
		$key = "{$this->_namespace}.{$key}";
      $_SESSION[$key] = $value;
   }
   
	/**
	 * deletes given key from the session
    * 
	 * @access public
	 */
	public function delete($key) {
		$key = "{$this->_namespace}.{$key}";
		$_SESSION[$key] = '';
		unset($_SESSION[$key]);
	}

	/**
	 * check if given $key exists in the session
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function exists($key) {
		$key = "{$this->_namespace}.{$key}";
		return isset($_SESSION[$key]);
	}
   
   /**
    * return to array
    */
   public function toArray()	{ return $_SESSION; }
	public function __get($key) {	return $this->read($key);	}
	public function __set($key, $value) { return $this->write($key, $value); }
	public function __unset($key)	{ $this->delete($key); }
	public function __isset($key)	{ return $this->exists($key);	}
}