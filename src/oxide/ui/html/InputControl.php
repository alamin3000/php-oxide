<?php
namespace oxide\ui\html;
use oxide\helper\Html;

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

   const 
      TYPE_TEXT = 'text',
      TYPE_PASSWORD = 'password';

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
	public function __construct($type, $name, $value = null, $label = null,  $attrbs = null) {
		// call controller constructor
		parent::__construct('input', $name, $value, $label,  $attrbs);
		
		// setup input type
		if(isset(Html::$inputTypes[$type])) {
			$this->_type = $type;
		} else {
			throw new \Exception('Invalid input type provided: ' . $type);
		}
		
      $this->type = $type;
      $this->setName($name);
	}
   
   public function setValue($value) {
      parent::setValue($value);
      $this->value = $value;
   }
}