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
   
   protected
      $_session = null;
   
   public function __construct(HelperContainer $container) {
      $this->_session = $container->getContext()->getSession();
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
			$value = $_SESSION[$key];
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
	public function set($value, $namespace = null) {
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}

		$_SESSION[$key] = $value;
	}
}


