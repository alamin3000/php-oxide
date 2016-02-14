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
   
   protected 
      $_type = null;


   /**
	 * construct the button control
	 * 
	 * @param string $name
	 * @param string $label
	 * @param array $attrbs
	 */
	public function __construct($name, $html = null, $label = null, array $attrbs = null) {
		parent::__construct($name, $html, $label,  $attrbs);
      
      $this->_tagName = 'button';
      $this->setHtml($html);
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
   
   public function setType($type) {
      $this->_type = $type;
      $this->setAttribute('type', $type);
   }
}