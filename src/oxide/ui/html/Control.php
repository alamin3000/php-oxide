<?php
namespace oxide\ui\html;
use oxide\base\ArrayString as String;

/**
 * Defines an abstract class for creating HTML Control
 *
 * privides functionalities for control name, value & label
 */
class Control extends Element implements FormAware { 
   public
      /**
       * @var Tag Tag for rendering label, if any
       */
      $labelTag = null,
      
      /**
       * @var Tag Tag for rendering error, if any
       */
      $errorTag = null,
           
      /**
       * @var Tag Tag for rendering info, if any
       */
      $infoTag = null,
           
      /**
       * @var Tag Tag for wrapping the control as a whole
       */
      $rowTag = null,
           
      /**
       * @var bool Indicates if label should wrap the control.
       */
   	$labelWrapsControl = true,
           
      /**
       * @var int Indicates label position (LEFT|RIGHT)
       */
      $labelPosition = self::LEFT;

   
   protected 
      $_name = null,
		$_label = null,
      $_value = null,
      $_data = null,
      $_error = null,
      $_info = null,
           
      /**
       * @var Form form container element for the control
       */
      $_form = null;
   
   const
   	LEFT = 1,
   	RIGHT = 2;


   /**
    * construct
    *
    * initializes the element.
    * @access public
    * @param string $name
    * @param string $value
    * @param string $label
    */
   public function __construct($name, $value = null, $label = null, $data = null) {
      parent::__construct();
      $this->setName($name);      
      if($label) $this->setLabel($label);
      if($value) $this->setValue($value);
      if($data) $this->setData($data);
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
    * @param Form $form
    */
   public function setForm(Form $form = null) {
      if($form === null) {
         if($this->_form) { // if form exists, we will remove the referene of this control first
            $this->_form->removeControlRef($this);
            $this->_form = null;
         }
         
         if($this->hasAttribute('form')) {
            $this->removeAttribute('form');
         }
      } else {
         $form->addControlRef($this);
         $this->setAttribute('form', $form->getName());
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
    * Before rendering starts, we need to notify form, if available.
    */
   protected function onRender() {
      parent::onRender();
      // notify form
      if(($form = $this->getForm())) {
         $form->onControlRender($this);
      }
   }
         
   /**
    * Perform additional rendering
    * 
    * @param ArrayString $buffer
    */
   protected function onRenderClose(String $buffer) {
      parent::onRenderClose($buffer);
      // render label, if available
      if(($label = $this->_label)) {
         $labelTag = ($this->labelTag) ? $this->labelTag : new Tag('label');
         
         // deal with wrapping and label positions
         if($this->labelWrapsControl) {
            if($this->labelPosition == self::LEFT) { 
               $buffer->prepend($label . ' '); 
            } else { 
               $buffer->append(' ' . $label); 
            }   
            $buffer->prepend($labelTag->renderOpen())->append($labelTag->renderClose());
         }
         else {
            $labelTag->setAttribute('for', $this->getName());
            if($this->labelPosition == self::LEFT) { 
               $buffer->prepend($labelTag->renderWithContent($label) . ' '); 
            } else {
               $buffer->append(' ' . $labelTag->renderWithContent($label)); 
            }
         }
      }
      
      // render info, if available
      if(($info = $this->getInfo())) {
         $infoTag = ($this->infoTag) ? $this->infoTag : new Tag('small');
         $buffer->append(' ' . $infoTag->renderWithContent($info));
      }
      
      // render error, if avalable
      if(($error = $this->getError())) {
         $errorTag = ($this->errorTag) ? $this->errorTag : new Tag('strong');
         $buffer->append(' ' . $errorTag->renderWithContent($error));
      }
      
      // wrap the control if rowtag provided
      if(($rowTag = $this->rowTag)) {
         $buffer->prepend($rowTag->renderOpen())->append($rowTag->renderClose());
      }
   }
   
   /**
    * 
    * @param type $key
    * @param \oxide\ui\html\Element $value
    */
   protected function onArrayAccessSet($key, $value) {
      throw new \Exception('Direct access to inner contents of Control is not allowed.  Use setData instead.');
   }
}