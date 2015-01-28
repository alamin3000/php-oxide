<?php
namespace oxide\ui\html;

/**
 * InputControl class
 *
 * represents an HTML INPUT form control
 * @package oxide
 * @subpackage ui
 */
class InputControl extends Control {
   protected
		$_type = null;
   
   public static
      $inputTypes = [
         'text' => true, 'submit' => true, 'button' => true, 'password' => true, 
         'hidden' => true, 'radio' => true, 'image' => true, 'checkbox' => true, 'file' => true ,
         'email' => true, 'url' => true, 'tel' => true, 'number' => true, 'range' => true, 'search' => true, 
         'color' => true, 'datetime' => true, 'date' => true, 'month' => true, 'week' => true, 
         'time' => true, 'datetime-local' => true, 'button' => true];

   /**
	 * construct the input control
	 *
	 * $type can be one of the html INPUT type (ex. 'text', 'password', 'submit')
	 * @param string $type
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $attrbs
	 */
	public function __construct($type, $name, $value = null, $label = null) {
		if(isset(self::$inputTypes[$type])) {
			$this->_type = $type;
		} else {
			throw new \Exception('Invalid input type provided: ' . $type);
		}
      
		parent::__construct($name, $value, $label);
      $this->setTag('input');
      $this->void = true;
      $this->_attributes['type'] = $type;
	}
   
   public function getType() {
      return $this->_type;
   }
   
   public function setValue($value) {
      parent::setValue($value);
      $this->_attributes['value'] = $value;
   }
}