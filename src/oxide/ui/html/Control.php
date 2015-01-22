<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;

/**
 * Defines an abstract class for creating HTML Control
 *
 * privides functionalities for control name, value & label
 */
class Control extends Element {   
   protected 
      $_errorTag = null,
      $_infoTag = null,
      $_labelTag = null,
      $_wrapperTag = null,
      $_error = null,
      $_info = null,
		$_label = null,
      $_value = null,
      
      /**
       * @var Form form container element for the control
       */
      $_form = null;

   /**
    * construct
    *
    * initializes the element.
    * @access public
    * @param string $name
    * @param string $value
    * @param string $label
    * @param array $attributes
    */
   public function __construct($tag, $name, $value = null, $label = null, array $attributes = null) {
      parent::__construct($tag, null, $attributes);
      $this->setName($name);      
      if($label) $this->setLabel($label);
      if($value) $this->setValue($value);
   }
   
   /**
    * Get the Label rendering tag
    * 
    * @return Tag
    */
   public function getLabelTag() {
      if($this->_labelTag == null) {
         $this->_labelTag = new Tag('label');
      }
      
      return $this->_labelTag;
   }

   /**
    * 
    * @return Tag
    */
   public function getErrorTag() {
      if($this->_errorTag == null) {
         $this->_errorTag = new Tag('strong');
      }
      
      return $this->_errorTag;
   }
   
   /**
    * 
    * @return Tag
    */
   public function getInfoTag() {
      if($this->_infoTag == null) {
         $this->_infoTag = new Tag('small');
      }
      
      return $this->_infoTag;
   }
   
   /**
    * 
    * @return Tag
    */
   public function getWrapperTag() {
      if($this->_wrapperTag == null) {
         $this->_wrapperTag = new Tag('p');
      }
      
      return $this->_wrapperTag;
   }
      
   /**
    * setting the name for the control
    * 
    * according to w3c validation, id attribute should be provided for html control
    * these will be set to attributes
    * @param type $name
    */
   public function setName($name) {
      $this->name = $name;
   }

   /**
    * get the name of this element
	 * @access public
    * @return string
    */
   public function getName() {
      return $this->name;
   }

   /**
    * sets value for the element.
    * @access public
    * @param string $value
    */
   public function setValue($value) {
      $this->_value = $value;
   }

   /**
    * performs filters on the value and returns it.
    * 
    * It decodes the Raw URL before returning.  URL ecoding is done automatically by PHP
    * @access public
    * @return string
    */
   public function getValue() {
      if(is_array($this->_value)) {
         return array_map('rawurldecode', $this->_value);
      }
      
		return rawurldecode($this->_value);
   }

   /**
    * set label for the control
    * @access public
    * @param string $str
    */
   public function setLabel($str) {
   	$this->_label = $str;
   }

   /**
    * get the label for the control
    * @access public
    * @return string
    */
   public function getLabel() {
   	return $this->_label;
   }
   
   /**
    * Set error string for the control
    * 
    * @param string $str
    */
   public function setError($str) {
      $this->_error = $str;
   }
   
   /**
    * Get error string for the control, if any
    * 
    * @return string
    */
   public function getError() {
      return $this->_error;
   }
   
   /**
    * Set info for the control.
    * 
    * This is additional info that can supplement the control's label
    * This is usually displayed after the control.
    * @param string $str
    */
   public function setInfo($str) {
      $this->_info = $str;
   }
   
   /**
    * Get the control info
    * 
    * @return string
    */
   public function getInfo() {
      return $this->_info;
   }

   /**
    * Set the form reference for this control
    * 
    * This will also set the control's attribute form appropriately (HTML5 standard)
    * @param \oxide\ui\html\Form $form
    */
   public function setForm(Form $form = null) {
      if($form === null) {
         if($this->_form) { // if form exists, we will remove the referene of this control first
            $this->_form->removeControlRef($this);
            $this->_form = null;
         }
         unset($this->form);
      } else {
         $form->addControlRef($this);
         $this->form = $form->id;
         $this->_form = $form;
      }
   }
   
   /**
    * Get the form reference for this control, if any
    * 
    * @return Form
    */
   public function getForm() {
      return $this->_form;
   }
   
   /**
    * Notify form, if available, before rendering the control
    * This will allow form to do any additional process/modifying
    * @param \oxide\util\ArrayString $buffer
    * @return boolean
    */
   protected function onPreRender(ArrayString $buffer) {
      if($this->_form) {
         // we have the control inside a form
         // we will need to inform the form about rendering
         $this->_form->onControlRender($this, $buffer);
      }
   }
  
   /**
    * Notify form, if available, after rendering the control
    * @param \oxide\util\ArrayString $buffer
    */
   protected function onPostRender(ArrayString $buffer) {
      $wrap = false;
      if($this->_label) { // render label if available
         $wrap = true;
         $label = $this->_label;
         if(isset($this->required)) {
            $label .= '*';
         }
         $buffer->prepend($this->getLabelTag()->renderWithContent($label));
      }
      
      if($this->_info) { // render info if available
         $wrap = true;
         $buffer->append($this->getInfoTag()->renderWithContent($this->_info));
      }
      
      if($this->_error) { // render error if available
         $wrap = true;
         $buffer->append($this->getErrorTag()->renderWithContent($this->_error));
      }
      
      if($wrap) { // wrap the control if required
         $wrapper = $this->getWrapperTag();
         $buffer->prepend($wrapper->renderOpen());
         $buffer->append($wrapper->renderClose());
      }
   }
}