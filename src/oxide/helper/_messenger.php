<?php
namespace oxide\helper;
use oxide\http\Session;

abstract class _messenger {
   const
      ERROR		= 'error',
      WARNING	= 'warning',
      ALERT		= 'alert',
      INFO		= 'info';

   
   /**
    *
    * @access private
    * @var type 
    */
	private static
      $_key		= "__phpoxide_helper_messager";

  
   /**
    * get the current flash message based on the namespace
    * @access public
    * @param type $namespace
    * @return type 
    */
   public static function get($namespace = null) {
		$session = Session::getInstance();
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}

		$value = null;
		if(isset($session->$key)) {
			$value = unserialize($session->$key);
			unset($session->$key);
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
	public static function set($value, $namespace = null, $type = self::INFO) {
		$session = Session::getInstance();
		$key = self::$_key;
		if($namespace) {
			$key .= "_{$namespace}";
		}

		$session->$key = serialize(new _messenger_message($value, $type, $namespace));
	}

   /**
    *
    * @access public
    * @param type $message
    * @param type $type
    * @param type $namespace
    * @return \_messenger_message 
    */
   public static function message($message, $type = self::ERROR,$namespace = null) {
      $obj = new _messenger_message();
      $obj->message = $message;
		$obj->namespace = $namespace;
      $obj->type = $type;
      $obj->timestamp = now();

      return $obj;
   }
   
   /**
    * @param type $namespace
    * @return type
    */
   public static function render($namespace = null) {
      $message = self::get($namespace);
      if(!$message) return;
      $class = 'template-' . $message->type;
      return _html::tag('mark', $message, array('class' => $class));
   }
   
}

class _messenger_message
{
   /**
    *
    * @access public
    * @var type 
    */
   public
      $message,
      $type,
		$namespace,
      $timestamp;

   
   /**
    *
    * @access public
    * @param type $message
    * @param type $type
    * @param type $namespace 
    */
   public function  __construct($message = null, $type = null, $namespace = null) {
      $this->message= $message;
      $this->type = $type;
      $this->timestamp = time();
   }
   
   /**
    * 
    * @access public
    */
   public function  __toString() {
      return (string) $this->message;
   }
}

