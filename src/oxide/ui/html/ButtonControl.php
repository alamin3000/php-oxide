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
	public function __construct($name, $html = null, array $attrbs = null) {
		parent::__construct($name, null, null,  $attrbs);
      
      $this->_attributes['type'] = $type;
      $this->setHtml($html);
	}
}