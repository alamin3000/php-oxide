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
    * 
    * @param type $name
    */
   protected function setName($name) {
      parent::setName($name);
      $this->setAttribute('name', $name);
   }
   
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
	public function __construct($name, $value = null, $label = null) {
		parent::__construct($name, $value, $label);
      $this->setTagName('input');
      $this->isVoid = true;
	}
   
   /**
    * 
    * @param type $type
    * @throws \Exception
    */
   public function setType($type) {
      if(isset(self::$inputTypes[$type])) {
			$this->_type = strtolower($type);
		} else {
			throw new \Exception('Invalid input type provided: ' . $type);
		}
      
      $this->setAttribute('type', $type);
   }
   
   /**
    * getType function.
    * 
    * @access public
    * @return void
    */
   public function getType() {
      return $this->_type;
   }
   
   /**
    * 
    * @param type $value
    */
   public function setValue($value) {
      parent::setValue($value);
      $this->setAttribute('value', $value);
   }
}