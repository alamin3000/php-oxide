<?php
namespace oxide\app\helper;
use oxide\http\Session;

class Flash  {
   /**
    *
    * @access private
    * @var type 
    */
	private static
      $_key		= "__oxide_helper_flash";
   
   const
   	TYPE_ERROR = 'error',
   	TYPE_ALERT = 'info',
   	TYPE_DEFAULT = '';
   
   protected
      /**
       * @var \oxide\http\Session
       */
      $_session = null;
   
   public function __construct(Session $session) {
      $this->_session = $session;
   }
   
   /**
    * Checks if flash message exists
    * 
    * @param string $namespace
    * @return bool
    */
   public function has($namespace = '') {
      $key = self::$_key . $namespace;
      return $this->_session[$key];
   }
  
   /**
    * get the current flash message based on the namespace
    * @access public
    * @param type $namespace
    * @return type 
    */
   public function get($namespace = null) {
		$key = self::$_key.$namespace;
      $session = $this->_session;
      
		$value = null;
		if($session->exists($key)) {
			$value = unserialize($session[$key]);
			unset($session[$key]);
		}

		return $value;
	}

   /**
    * sets the flash message within given namespace
    * @access public
    * @param type $value
    * @param type $namespace
    * @param type $type 
    */
	public function set($value, $namespace = null, $type = '') {
		$key = self::$_key.$namespace;
		
		$message = new FlashMessage($value, $type);
		$this->_session[$key] = serialize($message);
	}
}


class FlashMessage {
	public
		$message = null,
		$type = null;
		
	public function __construct($message, $type = null) {
		$this->message = $message;
		if($type !== null) $this->type = $type;
	}
	
	public function __toString() {
		return (string) $this->message;
	}
}


