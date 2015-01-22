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
	public function __construct($type, $name, $html = null, array $attrbs = null) {
		parent::__construct('button', $name, null, null,  $attrbs);
      $this->type = $type;
      $this->setHtml($html);
	}
}