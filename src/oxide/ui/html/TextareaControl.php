<?php
namespace oxide\ui\html;

/**
 * TextareaControl
 *
 * Creates new TEXTAREA html form control
 * @package oxide
 * @package ui
 */
class TextareaControl extends Control {
	/**
	 * construct the textarea control
	 * 
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $attrbs
	 */
	public function __construct($name, $value = '', $label = null,  $attrbs = null) {
		parent::__construct('textarea', $name, $value, $label,  $attrbs);
		$this->setHtml($value);
      $this->name = $name;		
	}
   
   public function setValue($value)  {
      parent::setValue($value);
      $this->setHtml($value);
   }

	/**
	 * renders
	 * @return string
	 */
	public function renderInner() {
		return self::escape($this->getValue());
	}	
}