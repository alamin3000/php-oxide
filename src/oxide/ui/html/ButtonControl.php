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
	 * @param string $label
	 * @param array $attrbs
	 */
	public function __construct($type, $name, $value = null, $html = null, array $attrbs = null) {
		parent::__construct('button', $name, $value, null,  $attrbs);
      $this->type = $type;
      $this->setValue($value);
      $this->setHtml($html);
	}
   
      
   public function setValue($value) {
      parent::setValue($value);
      $this->value = $value;
   }
}