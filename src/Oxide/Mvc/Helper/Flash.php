<?php
namespace oxide\app\helper;
use oxide\http\Context;

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
   
   public function __construct(Context $context) {
      $this->_session = $context->getSession();
   }
  
   /**
    * get the current flash message based on the namespace
    * @access public
    * @param type $namespace
    * @return type 
    */
   public function flash($value = null, $type = '') {
		$key = self::$_key;
      $session = $this->_session;
      if($value === null) {
         if(isset($session[$key])) {
            $value = unserialize($session[$key]);
            unset($session[$key]);
            return $value;
         } else {
            return false;
         }
		} else {
         $session[$key] = serialize(new FlashMessage($value, $type));
      }
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


