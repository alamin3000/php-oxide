<?php
namespace oxide\app\helper;

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
   	TYPE_ALERT = 'alert',
   	TYPE_DEFAULT = '';
   
   protected
      $_session = null;
   
   public function __construct(HelperContainer $container) {
      $this->_session = $container->getContext()->get('session');
   }
   
   public function has($namespace = null) {
      $key = self::$_key;
      if($namespace) {
			$key .= "_{$namespace}";
		}
      
      return isset($_SESSION[$key]);
   }
  
   /**
    * get the current flash message based on the namespace
    * @access public
    * @param type $namespace
    * @return type 
    */
   public function get($namespace = null) {
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}

		$value = null;
		if(isset($_SESSION[$key])) {
			$value = unserialize($_SESSION[$key]);
			unset($_SESSION[$key]);
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
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}
		
		$message = new FlashMessage($value, $type);

		$_SESSION[$key] = serialize($message);
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


