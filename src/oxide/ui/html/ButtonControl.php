<?php
namespace oxide\ui\html;

/**
 * Button class
 *
 * represents an HTML BUTTON control
 * @package oxide
 * @subpackage ui
 */
class ButtonControl extends Control {
   /**
	 * construct the button control
	 * 
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $attrbs
	 */
	public function __construct($type, $name, $value = null, $label = null,  $attrbs = null) {
		parent::__construct('button', $name, $value, $label,  $attrbs);
      $this->type = $type;
	}
   
   public function setValue($value) {
      parent::setValue($value);
      $this->html($value);
   }
}