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
      $_processedValues = null,
      $_processed = false,
      $_controlPreparedCallback = null,
		$_errorMessage = 'Form validation failed.',
		$_successMessage = 'Form submission was successful.';

	static protected
      $_form_ids = [],
		$_counter = 0;
   
   private 
      /**
       * @var validation\ValidationProcessor holds input validation for the form
       * @access private
       */
      $_validationProcessor = null,
		$_formid_key = null,
		$_formid = null;
             
   const
      TYPE_STANDARD = 1,
      TYPE_INLINE = 2,
      METHOD_POST = 'post',
      METHOD_GET = 'get';


   /**
    * constructor
    *
	 * @param string $method value can be 'post' or 'get'
	 * @param string $action url here form processing will be performed
    * @param string $name name/id of the form.  this is important when there are multiple forms in the page
    */
	public function __construct($method = self::METHOD_POST, $action = null, $name = null, $attrib = []) {
      self::$_counter++;
      parent::__construct('form', null, $attrib);
      if(!$action) $action = filter_input(INPUT_SERVER, 'REQUEST_URI');
      if(!$name) $name = "_form-" . self::$_counter;
      if($method == self::METHOD_POST) $values = filter_input_array(INPUT_POST);
      else if($method == self::METHOD_GET) $values = filter_input_array(INPUT_GET);
      else throw new \Exception('Unknown Form method : '. $method);
      if($_FILES) $values = array_merge($values, $_FILES);
            
      $this->name = $name;
      $this->id = $name;
      $this->_method = $this->method = $method;
		$this->_action = $this->action = $action;      
      $this->_values = $values;    // store the raw values
		$this->_generateSubmitId($name);   // generating a unique id for the form
      $this->addControl($this->getIdentifierControl());
	}
   
   /**
    * Get the Error rendering tag
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
    * Get the success tag
    * 
    * @return Tag
    */
   public function getSuccessTag() {
      if($this->_successTag == null) {
         $this->_successTag = new Tag('b');
      }
      
      return $this->_successTag;
   }
   
   /**
    * Header element.
    * 
    * Initially the element's tag is set to 'p'.
    * @return Element
    */
   public function getHeaderElement() {
      if($this->_headerElement == null) {
         $this->_headerElement = new Element('p');
      }
      
      return $this->_headerElement;
   }
   
   /**
    * Footer Element
    * 
    * Initially elements tag is set to 'p'
    * @return Element
    */
   public function getFooterElement() {
      if($this->_footerElement == null) {
         $this->_footerElement = new Element('p');
      }
      
      return $this->_footerElement;
   }
   
   /**
    * Set the success message for the form
    * 
    * This message will be displayed after form is submitted and successfully processed.
    * @param string $message
    * @return string|null
    */
   public function successMessage($message = null) {
      if($message) $this->_successMessage = $message;
      else return $this->_successMessage;
   }
   
   /**
    * Set the error message for the form
    * 
    * This message will be displayed after for is submitted and validation error occured.
    * @param string $message
    * @return null|string
    */
   public function errorMessage($message = null) {
      if($message) $this->_errorMessage = $message;
      else return $this->_errorMessage;
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
   
   /**
    * Callback that will be called after form has prepared a control
    * 
    * @param \Closure $callback
    * @return \Closure
    */
   public function controlPreparedCallback(\Closure $callback = null) {
      if($callback) $this->_controlPreparedCallback = $callback;
      else return $this->_controlPreparedCallback;
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
         $header = $this->getHeaderElement();
         if(!$result->isValid()) {
            $strong = new Tag('strong');
            $header[] = $strong->renderWithContent($this->_errorMessage);
            if($result->hasError($this->getId())) {
               $header[] = new Tag('br');
               $header[] = $strong->renderWithContent($result->getErrorString($this->getId()));
            } else {
               
            }
         } else { // for submission success
            $b = new Tag('b');
            $header[] = $b->renderWithContent($this->_successMessage);
         }
         
         return $header->render();
      } else {
         if($this->_headerElement) return $this->_headerElement->render ();
         else return '';
      }

	}

   /**
    * return form footer
    * 
    * @param Form $form
    * @return string
    */
	public function renderFormFooter() {
      if($this->_footerElement) return $this->_footerElement->render ();
      else return '';
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
			$control->required = "required"; // 'required' attribute is part of (HTML5)
		}
      
      if($result->hasError($name)) { // stringify error messages
         $control->setError($result->getErrorString($name));
		}
      
      if($value !== null) {
         $control->setValue($value); // setting the form submitted value
      }
      
      $callback = $this->controlPreparedCallback();
      if($callback) $callback($control, $this);
   }
    
   protected function onPreProcess(validation\Result $result) {}
   protected function onPostProcess(validation\Result $result, array $processedvalues = null) {}
}