<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;

/**
 * Defines an abstract class for creating HTML Control
 *
 * privides functionalities for control name, value & label
 */
class Control extends Element {
   public
   	$labelWrapsControl = false,
      $labelPosition = self::LABEL_LEFT;
           
   protected 
      $_name = null,
      $_error = null,
      $_info = null,
		$_label = null,
      $_value = null,
      $_data = null,
      /**
       * @var Form form container element for the control
       */
      $_form = null,
           
      $_infoTag = null,
      $_errorTag = null,
      $_labelTag = null;
   
   const
   	LABEL_LEFT = 1,
   	LABEL_RIGHT = 2,
   	LABEL_OUTER = 3;

   /**
    * construct
    *
    * initializes the element.
    * @access public
    * @param string $name
    * @param string $value
    * @param string $label
    */
   public function __construct($name, $value = null, $label = null, $data = null, array $attributes = null) {
      parent::__construct();
      $this->setName($name);      
      if($label) $this->setLabel($label);
      if($value) $this->setValue($value);
      if($data) $this->setData($data);
      if($attributes) $this->setAttributes ($attributes);
   }
      
   /**
    * setting the name for the control
    * 
    * according to w3c validation, id attribute should be provided for html control
    * these will be set to attributes
    * @param type $name
    */
   protected function setName($name) {
      $this->_name = $name;
   }

   /**
    * get the name of this element
	 * @access public
    * @return string
    */
   public function getName() {
      return $this->_name;
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
    * Get control's data
    * 
    * @return mixed
    */
   public function getData() {
      return $this->_data;
   }
   
   /**
    * Set control's data
    * 
    * @param mixed $data
    */
   public function setData($data) {
      $this->_data = $data;
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
         unset($this->_attributes['form']);
      } else {
         $form->addControlRef($this);
         $this->_attributes['form'] = $form->getName();
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
    * Get the Tag object used for rendering the label tag
    * 
    * If no object is assigned, a new tag object will be created
    * @return Tag
    */
   public function getLabelTag() {
      if($this->_labelTag === null) {
         $this->_labelTag = new Tag('label', ['for' => $this->getName()]);
      }
      
      return $this->_labelTag;
   }
   
   /**
    * Set tag object to be used for the label rendering
    * 
    * @param Tag $tag
    */
   public function setLabelTag(Tag $tag = null) {
      $this->_labelTag = $tag;
   }
   
   /**
    * Get the Tag instance used for rendering control info
    * 
    * If no object instance is assigned, new instance will be created
    * Defaults to 'small' tag
    * @return Tag
    */
   public function getInfoTag() {
      if($this->_infoTag === null) {
         $this->_infoTag = new Tag('small');
      }
      
      return $this->_infoTag;
   }
   
   /**
    * 
    * @param Tag $tag
    */
   public function setInfoTag(Tag $tag) {
      $this->_infoTag = $tag;
   }
   
   /**
    * Get the error tag
    * 
    * If no tag instance is assigned, new instance will be created
    * @return Tag
    */
   public function getErrorTag() {
      if($this->_errorTag === null) {
         $this->_errorTag = new Tag('strong');
      }
      
      return $this->_errorTag;
   }
   
   /**
    * 
    * @param Tag $tag
    */
   public function setErrorTag(Tag $tag) {
      $this->_errorTag = $tag;
   }
   
   /**
    * Notify form, if available, before rendering the control
    * This will allow form to do any additional process/modifying
    * @param \oxide\util\ArrayString $buffer
    * @return boolean
    */
   protected function onRender() {
	   // need to call this before everythign else
	   // since form may add any additional information
	   if($this->_form) {
         // we have the control inside a form
         // we will need to inform the form about rendering
         $this->_form->onControlRender($this);
      }
      
	   // label setup
	   if($this->_label) {
		   $labelTag = $this->getLabelTag();
		   $label = null;
		   if($this->labelWrapsControl) {
			   print 'inside';
			   $label = $this->_label;
			   $this->wrappers[] = $labelTag;
		   } else {
			   print 'nono';
			   $label = $labelTag->renderContent($this->_label);
		   }
		   
		   if($this->labelPosition == self::LABEL_RIGHT) {
			   $this->after[] = $label;
		   } else {
			   $this->before[] = $label;
		   }
	   }
	   
	   // info setup
	   if($this->_info) {
		   $this->after[] = $this->getInfoTag()->renderContent($this->_info);
	   }
	   
	   // error setup
	   if($this->_error) {
		   $this->after[] = $this->getErrorTag()->renderContent($this->_error);
	   }
   }
}