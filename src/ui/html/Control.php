<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;

/**
 * Defines an abstract class for creating HTML Control
 *
 * privides functionalities for control name, value & label
 * @package oxide
 * @subpackage ui
 * @abstract
 */
class Control extends Element {   
   public
      $labelTag = null;
   
   protected 
      $_labelTag = null,
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
   public function __construct($tag, $name, $value = null, $label = null, $attributes = null) {
      parent::__construct($tag, null, $attributes);
      
      // we will need to enforce the name for all controls
      if(!$name) {
         throw new \Exception('Name for control must be provided.');
      }

      // setting value and lable
      $this->setLabel($label);
      $this->setValue($value);
      $this->setName($name);
   }
   
   /**
    * Get the Label rendering tag
    * 
    * @return \oxide\ui\html\Tag
    */
   public function getLabelTag() {
      if($this->labelTag == null) {
         $this->labelTag = new Tag('label');
      }
      
      return $this->labelTag;
   }
   
   /**
    * Set the Label rendering tag
    * @param \oxide\ui\html\Tag $tag
    */
   public function setLabelTag(Tag $tag) {
      $this->labelTag = $tag;
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
    * Render the current label with label tag
    * @return string
    */
   public function renderLabel() {
      if($this->getLabel())
         return self::renderTag($this->getLabelTag(), $this->getLabel());
      else return '';
   }
   
   /**
    * Override this method to position label element
    * @param ArrayString $buffer
    */
   protected function onRenderLabel(ArrayString $buffer) {
      $buffer[] = $this->renderLabel();
   }

   
   /**
    * Notify form, if available, before rendering the control
    * @param \oxide\util\ArrayString $buffer
    * @return boolean
    */
   protected function onPreRender(ArrayString $buffer) {
      if($this->_form) {
         // we have the control inside a form
         // we will need to inform the form about rendering
         $this->_form->onPreControlRender($this, $buffer);
      }
      
      $this->onRenderLabel($buffer);
      return true;
   }
  
   /**
    * Notify form, if available, after rendering the control
    * @param \oxide\util\ArrayString $buffer
    */
   protected function onPostRender(ArrayString $buffer) {
      if($this->_form) {
         $this->_form->onPostControlRender($this, $buffer);
      }
   }
}