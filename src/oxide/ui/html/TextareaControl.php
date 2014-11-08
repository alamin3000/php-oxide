<?php
namespace oxide\ui\html;
use oxide\helper\_html;
use oxide\util\ArrayString;

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
		$this->html($value);
      $this->name = $name;		
	}
   
   public function setValue($value)  {
      parent::setValue($value);
      $this->html($value);
   }

	/**
	 * renders
	 * @return string
	 */
	public function renderInnerTag() {
		return _html::escape($this->getValue());
	}	
}