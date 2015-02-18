<?php
namespace oxide\base;
use oxide\base\pattern\PropertyAccessTrait;

/**
 * A Generic object
 * Supports readonly properties and modification tracking
 */
class Object 
   extends AbstractClass 
   implements Stringify, \Serializable {
   use PropertyAccessTrait;
   
   protected
   	$_readyonly_vars = [];
   
   /**
    * __construct function.
    * 
    * @access public
    * @return void
    */
   public function __construct() {
   }
   
   
   
   /**
    * __toString function.
    * 
    * @access public
    * @return void
    */
   public function __toString() {
      return "[Object: ". get_called_class() . "]";
   }
   
   /**
    * Check if given string is serialized
    * @access public
    * @static
    * @param mixed $str
    * @return void
    */
   public static function serialized($data) {
	   // if it isn't a string, it isn't serialized
		return (@unserialize($data) !== false || $string == 'b:0;');
   }
   
   /**
    * serialize function.
    * 
    * @access public
    * @param mixed $str
    * @return void
    */
   public function serialize($str) {
	   return serialize([
		   'data' => $this->_t_property_storage,
		   'readonly' => $this->_readyonly_vars,
	   ]);
   }
   
   
   /**
    * unserialize function.
    * 
    * @access public
    * @param mixed $str
    * @return void
    */
   public function unserialize($str) {
		$data = unserialize($str);
		$this->_t_property_storage = $data['data'];
		$this->_readyonly_vars = $data['readonly'];
   }
   
   
   protected function _t_prop_access_set($key, $value) {
	   if($this->_readyonly_vars[$key]) {
		   throw new \Exception("Key `{$key}` is readonly in Object: " . get_called_class());
	   }
   }
   protected function _t_prop_access_unset($key, $value) {
	   $this->_t_prop_access_set($key, $value);
   }

}