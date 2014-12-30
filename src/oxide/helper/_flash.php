<?php
namespace oxide\helper;

class _flash  {
   /**
    *
    * @access private
    * @var type 
    */
	private static
      $_key		= "__oxide_helper_flash";
   
   public static function has($namespace = null) {
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
   public static function get($namespace = null) {
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
	public static function set($value, $namespace = null) {
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}

		$_SESSION[$key] = $value;
	}
}


