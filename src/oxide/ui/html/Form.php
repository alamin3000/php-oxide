<?php
namespace oxide\ui\html;
use oxide\validation;
use oxide\util\ArrayString;

/**
 * Form class
 *
 * Representing html FORM element
 *
 * provides support for managing form controls, validating user input as well as
 * rendering HTML.
 *
 * @package oxide
 * @subpackage ui
 */
class Form extends Element {
   use ControlAccessTrait;
   
   public
      $labelTag = null,
      $errorTag = null,
      $successTag = null,
      $infoTag = null,
      $rowTag = null,
  		$submitErrorMessage = 'Form validation failed.',
		$submitSuccessMessage = 'Form submission was successful.',
      $requiredIndicator = '*',
      $requiredMessage = '* Indicates required field(s).';

   protected
      $_errorTag = null,
      $_successTag = null,
      $_headerElement = null,
      $_footerElement = null,
      $_controlRefs = [],
      $_method = null,
      $_action = null,
		$_result = null,
      $_values = null,
      $_name = null,
      $_processedValues = null,
      $_processed = false,
      $_controlPreparedCallback = null,
      /**
       * @var validation\ValidationProcessor holds input validation for the form
       * @access private
       */
      $_validationProcessor = null;

	static protected
      $_form_ids = [],
		$_counter = 0;
   
   private 
		$_formid_key = null,
		$_formid = null;
             
   const
      METHOD_POST = 'post',
      METHOD_GET = 'get';


   /**
    * constructor
    *
	 * @param string $method value can be 'post' or 'get'
	 * @param string $action url here form processing will be performed
    * @param string $name name/id of the form.  this is important when there are multiple forms in the page
    */
	public function __construct($name = null, $method = self::METHOD_POST, $action = null, $attrib = []) {
      self::$_counter++;
      parent::__construct('form', null, $attrib);
      if(!$action) $action = '';
      if(!$name) $name = "_form-" . self::$_counter;
      if($method == self::METHOD_POST) $values = filter_input_array(INPUT_POST);
      else if($method == self::METHOD_GET) $values = filter_input_array(INPUT_GET);
      else throw new \Exception('Unknown Form method : '. $method);
      if($_FILES) $values = array_merge($values, $_FILES);

      $this->_name = $name;
      $this->setMethod($method);
		$this->setAction($action);      
      $this->setValues($values);    // store the raw values
      $this->_generateSubmitId($name);   // generating a unique id for the form
	}
   
   /**
    * Set the form method (POST|GET)
    * 
    * @param string $method
    */
   public function setMethod($method) {
      $this->_attributes['method'] = $method;
   }
   
   /**
    * Get the form submit method
    * 
    * @return string
    */
   public function getMethod() {
      return $this->_attributes['method'];
   }
   
   /**
    * Set the form's action
    * 
    * @param string $action
    */
   public function setAction($action) {
      $this->_attributes['action'] = $action;
   }
   
   /**
    * Get the form's action
    * 
    * @return string
    */
   public function getAction() {
      return $this->_attributes['action'];
   }
   
   /**
    * Get the form's name
    * 
    * @return string
    */
   public function getName() {
      return $this->_name;
   }
      
   /**
	 * generate unique form id
    *
    * this is the unique id for the form
	 * @access private
	 * @return string
	 */
	private function _generateSubmitId($name = null) {
      if($name) $key = $name;
      else $key = '';
      $key = md5($key);
      
      if(isset(self::$_form_ids[$key])) throw new \Exception('Duplicate Form key was generated. Please check your Form name property.');

      $this->_formid_key = $key;
      $id = uniqid();
      $this->_formid = $id;
      self::$_form_ids[$key] = $id;
	}
   
	/**
	 * get the form identifier value
	 *
	 * this value along with the form identifier key must be present
	 * in order to process this form
	 * @return string
	 */
	public function getId() {
		return $this->_formid;
	}

	/**
	 * get the key for the form identifier
	 *
	 * this is used in the <input> tag in "name" attribute
	 * @return string
	 */
	public function getKey() {
		return $this->_formid_key;
	}

   /**
    * Returns Form value for the given $key
    *
    * Will always return SUBMITTED/RAW value by the user. NOT the control value
    * Filtered values will be returned by process() method
    * @see process()
    * @param string $key
    */
   public function getValue($key) {
      return (isset($this->_values[$key])) ? $this->_values[$key] : null;
   }
   
   /**
    * 
    * @param string $key
    * @param string $value
    */
   public function setValue($key, $value) {
      $this->_values[$key] = $value;
   }
   
   /**
    * 
    * @param arrray $values
    * @param boolean $merge
    */
   public function setValues($values, $merge = false) {
      if($merge) {
         $this->_values = array_merge($this->_values, $values);
      } else {
         $this->_values = $values;
      }
   }
   
   /**
    * Returns all submitted values
    * 
    * Always returns submitted/raw values by user
    * processed/filtered values will be returned by the process() method
    * @return array Submitted values, if any
    */
   public function getValues() {
      return $this->_values;
   }
   
   /**
    * return processed value for the $key if avaliable 
    * 
    * if the form still hasn't processed, it will return null
    * @param string $key
    * @return string
    */
   public function getProcessedValue($key) {
      if(!$this->_processed) return null;
      
      if(isset($this->_processedValues[$key])) return $this->_processedValues[$key];
      else return null;
   }
   
   /**
    * return all processed values
    * 
    * if form hasn't been processed yet, it will return null
    * @return array
    */
   public function getProcessedValues() {
      if(!$this->_processed) return null;
      
      return $this->_processedValues;
   }
   

	/**
	 * resets all form values
	 */
   public function reset() {
      $key = null;
      if($this->isSubmitted()) {
         $key = $this->_values[$this->_formid_key];
      }
		$this->_values = [];
   }
   
   /**
    * Stores a reference of the given Control.
    * 
    * This is used to easiy access all controls belong to the form.
    * Usually this method would be automatically called by Control instance
    * when setForm is called 
    * @param Control $control
    */
   public function addControlRef(Control $control) {
      $this->_controlRefs[$control->getName()] = $control;
   }
   
   /**
    * Removes a reference to the control
    * 
    * This will remove an assoication of the control from the form
    * This does not necessery removes the control from the 
    * @param \oxide\ui\html\Control $control
    */
   public function removeControlRef(Control $control) {
      unset($this->_controlRefs[$control->getName()]);
   }
   
   /**
    * Remove a control by given name from the collection
    * 
    * If success, removed control will be returned.
    * @param string $name
    * @return oxide\ui\html\Control
    */
   public function removeControl($name) {
      $control = $this->getControl($name);
      if($control) {
         $this->removeControlRef($control);
         // now remove
         $parent = $control->getParent();
         if($parent) {
            $pos = $parent->search($control);
            if($pos !== FALSE) {
               unset($parent[$pos]);
            }
         }
      }
      
      
      
      return $control;
   }
   
   /**
    * Add a control
    * 
    * @param Control $control
    */
   public function addControl(Control $control) {
      $this->append($control);
   }
   
   /**
    * Get control $name from the 
    * @param type $name
    * @return type
    */
   public function getControl($name) {
      return (isset($this->_controlRefs[$name])) ?
         $this->_controlRefs[$name] :
         null;
   }
   
   public function getControls() {
      return $this->_controlRefs;
   }
   
	/**
	 * Checks if form has been submitted or not
    * Optionally checks if form submit validation using session
    * @param boolean $session_check this checks the submit with session, to make sure it is not form spoofing
	 * @return bool
	 */
   public function isSubmit() { return $this->isSubmitted(); }
	public function isSubmitted() {   
      static $issubmit = null;
      
      
      // if once submit passed, we will return true
      // multiple check/verification is not needed
      if($issubmit == true) return true;
      
		/*
		 * only way to find out if the form is submitted or not is to first check
		 * if GET/POST value is preset with the form id
		 * GET/POST is only available at input validator, not the form itself.
		 */
      $key = $this->_formid_key;
		$formid = $this->getValue($key);  // secret from form
      
      // if formid is found, then we know the form was submitted
		if($formid) {    
         $issubmit = true;
		} else $issubmit = false;
      
      return $issubmit;
	}
   
   /**
    * Set the validation processor for the form
    * 
    * @param \oxide\validation\ValidationProcessor $processor
    */
   public function setValidationProcessor(validation\ValidationProcessor $processor) {
      $this->_validationProcessor = $processor;
   }

   /**
    * Get the form validation processor
    * @return validation\ValidationProcessor
    */
   public function getValidationProcessor() {
      if($this->_validationProcessor == null) {
         $this->_validationProcessor = new validation\ValidationProcessor();
      }
      
      return $this->_validationProcessor;
   }

   /**
	 * returns the form validation result
	 *
	 * @return validation\Result
	 */
	public function getResult() {
		if($this->_result == null) {
			$this->_result = new validation\Result();
		}
		
		return $this->_result;
	}

	/**
    * process the form
    *
    * returns filtered values if validation passes.  Otherwise null is passed.
    * @param validation\Result $result
    * @return null|array
    */
   public function process(validation\Result &$result = null) {
      // if form is disabled,
      // we will not process this
      if(isset($this->disabled)) {
         // this is an error
         throw new \Exception('Form is disabled, therefore cannot be processed.');
      }
      
		if($result) {
			$this->_result = $result;
		} else {
			$result = $this->getResult();
		}
      
      // notify internal event
      $this->onPreProcess($result);

      // now we get the processed/filtered values
      // if form process was not valid, this will return null
      $validationProcessor = $this->getValidationProcessor();
      $processedValues = $validationProcessor->process($this->_values, $result);
                        
      // indicate the form has been processed already
      $this->_processed = true;
      $this->_processedValues = $processedValues;
      // notify internal event
      $this->onPostProcess($result, $processedValues);
      return $processedValues;
   }
   
   /**
    * return bool
    */
   public function isProcessed() {
      return $this->_processed;
   }
   
   /**
    * Get the form identifier control.
    * 
    * This is automatically included during form's render process
    * This is hidden field to identify the for for submit and processing.
    * Must be included for the form to work properly.  
    * @return \oxide\ui\html\InputControl
    */
   public function getIdentifierControl() {
      return new InputControl('hidden', $this->_formid_key, $this->_formid);
   }
   
   public function getErrorTag() {
      if($this->errorTag === null) {
         $this->errorTag = new Tag('strong');
      }
      
      return $this->errorTag;
   }
   
   public function getRowTag() {
      if($this->rowTag === null) {
         $this->rowTag = new Tag('p');
      }
      
      return $this->rowTag;
   }
   
   
   /**
    * Callback that will be called after form has prepared a control
    * 
    * @param \Closure $callback
    * @return \Closure
    */
   public function setControlPrepareCallback(\Closure $callback = null) {
      $this->_controlPreparedCallback = $callback;
   }
   
   public function getControlPrepareCallback() {
      return $this->_controlPreparedCallback;
   }
   
   public function onRender() {
      parent::onRender();
      $this->_attributes['name'] = $this->_attributes['id'] = $this->_name;
      if(!$this->errorTag) $this->errorTag = new Tag ('strong');
      if(!$this->infoTag) $this->infoTag = new Tag('small');
      if(!$this->successTag) $this->successTag = new Tag('b');
      if(!$this->rowTag) $this->rowTag = new Tag('p');
   }
   
   /**
    * render for header
    *
    * @param Form $form
    * @return string
    */
	public function renderFormHeader() {            
		$result = $this->getResult();
      if($this->isProcessed()) {
         $msgs = '';
         if(!$result->isValid()) {
            $msgs .= $this->errorTag->renderContent($this->submitErrorMessage);
            if($result->hasError($this->getId())) {
               $msgs .= $this->errorTag->renderContent($result->getErrorString($this->getId()));
            }
         } else { // for submission success
            $msgs .= $this->successTag->renderContent($this->submitSuccessMessage);
         }
         
         return $this->rowTag->renderContent($msgs);
      }
	}

   /**
    * return form footer
    * 
    * @param Form $form
    * @return string
    */
	public function renderFormFooter() {
      $str = '';
      if($this->getValidationProcessor()->isRequired()) {
         $str .= $this->rowTag->renderContent(
                 $this->infoTag->renderContent($this->requiredMessage));
      }
      
      $str .= $this->getIdentifierControl()->render();
      return $str;
	}
   
	/**
	 * overriding the inner html render routine
	 *
    * We will render the header and footer for the form in addition to the inner tag (controls)
	 * @param /oxide/ui/Element $element
	 */
	public function renderInner() {
		return 
         $this->renderFormHeader() .
         parent::renderInner() .
         $this->renderFormFooter();
	}

   /**
    * This method is called by Control objects before it starts rendering
    * 
    * This method basically update the control if required.
    * @param Control $control
    * @param ArrayString $buffer Holds the current rendrered buffer for the control
    */
   public function onControlRender(Control $control) {
		$name          = $control->getName();      
      $value         = $this->getValue($name);
		$validation    = $this->getValidationProcessor();
		$result      = $this->getResult();
      
      if($validation->isRequired($name)) {
			$control->setAttribute('required'); // 'required' attribute is part of (HTML5)
         $control->setLabel($control->getLabel() . $this->requiredIndicator);
		}
      
      if($result->hasError($name)) { // stringify error messages
         $control->setError($result->getErrorString($name));
		}
      
      if($value !== null) {
         $control->setValue($value); // setting the form submitted value
      }
      
      $control->wrappers[] = $this->getRowTag();
      
      $callback = $this->_controlPreparedCallback;
      if($callback) $callback($control, $this);
   }
   
   protected function _t_array_access_set($key, $value) {
      parent::_t_array_access_set($key, $value);
      if($value instanceof Control ||
         $value instanceof Fieldset) {
         $value->setForm($this);
      }
   }
   
   protected function _t_array_access_get($key) {
      parent::_t_array_access_get($key);
   }
   
   protected function _t_array_access_unset($key, $value) {
      parent::_t_array_access_unset($key, $value);
      if($value instanceof Control ||
         $value instanceof Fieldset) {
         $value->setForm(null);
      }
   }

   protected function onPreProcess(validation\Result $result) {}
   protected function onPostProcess(validation\Result $result, array $processedvalues = null) {}
}