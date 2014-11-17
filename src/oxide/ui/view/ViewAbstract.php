<?php
namespace oxide\ui\view;
use oxide\ui\Renderer;
use oxide\util\pattern\PropertyAccessTrait;
/**
 * View Object
 * 
 * represent a physical html page.
 * holds variables
 * renders the represented html page
 *
 * @package oxide
 * @subpackage application
 */
abstract class ViewAbstract implements Renderer {
   use PropertyAccessTrait;
   
   public
      $contentType = 'text/plain',
      $encoding = 'UTF-8';
   
	private static
		$_sharedData = [];
   
   /**
    * @param string $script
    * @param string $title
    * @param string $identifier
    */
   public function __construct() {
   }

	/**
	 * get assigned value
	 *
	 * this is an alternate method to directly accessing value/property
	 * one advantage is that, it has default value mechanism
	 * @access public
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($var, $default = null) {
		if(isset($this->_t_property_storage[$var])) {
			return $this->_t_property_storage[$var];
		}
		
		if(isset(self::$_sharedData[$var])) {
			return self::$_sharedData[$var];
		}
		
		return $default;
	}
   
   /**
    * 
    * @param type $key
    * @param type $val
    */
   public function set($key, $val) {
      $this->_t_property_storage[$key] = $val;
   }
	
	/**
	 * share variable with all view instances
    * 
    * @access public
	 * @return 
	 * @param string $key
	 * @param mixed $value
	 */
	public static function share($key, $value) {
		self::$_sharedData[$key] = $value;
	}
	
	/**
	 * return shared value
    * 
    * @access public
	 * @return 
	 * @param string $key
	 * @param mixed $default
	 */
	public static function shared($key, $default = null) {
		if(isset(self::$_sharedData[$key])) {
			return self::$_sharedData[$key];
		} else {
			return $default;
		}
	}
   
   /**
    * Set data
    * @param array $data
    */
   public function setData($data) {
      $this->_t_property_storage = $data;
   }
   
   /**
    * Get data
    * @return array
    */
   public function getData() {
      return $this->_t_property_storage;
   }
   

	/**
	 * string representation of the View
	 * 
	 * this is simply rendered content
    * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->render(); 
	}
}