<?php
namespace oxide\validation;
/**
 * 
 */
class ValidationProcessor implements Processor {
   protected
      /**
       * @var FilterContainer Description
       */
      $_filters = null,
      /**
       * @var ValidatorContainer Description
       */
      $_validators = null,
      /**
       * @var ProcessorContainer Description
       */
      $_processors = null,
           
      $_preProcessCallbacks = [],
      $_postProcessCallbacks = [],
           

      $_requiredKeys = array(),
      $_missingRequiredKeys = array();
   
   const
      NOTIFICATION_PRE_PROCESS = 'OxideValidationProcessorPreProcess',
      NOTIFICATION_POST_PROCESS = 'OxideValidationProcessorPostProcess';
   
   public function __construct()  {
   }
   
   /**
    * 
    * @param \oxide\validation\ValidationComponent $component
    * @param type $key
    */
   public function addValidationComponent(ValidationComponent $component, $key = null) {
      $this->getFilterContainer()->addFilter($component, $key);
      $this->getValidatorContainer()->addValidator($component, $key);
      $this->getProcessorContainer()->addProcessor($component, $key);
   }

  /**
    * set required variables
    * 
    * @param array $vars names of the variables those are required
    */
   public function setRequired($vars) {
      if(is_array($vars)) {
         $this->_requiredKeys = $vars;
      }
   }

   /**
    * Add key(s) whos value is required
    * 
    * @param mixed $vars can be single key, or arry of keys
    */
	public function addRequired($vars) {
		if(!is_array($vars)) $vars = array($vars);

		foreach($vars as $var) {
         if(in_array($var, $this->_requiredKeys)) continue;
			$this->_requiredKeys[] = $var;
		}
	}

   /**
    * Returns array of keys, whos values are required
    * 
    * @return array
    */
	public function getRequiredArray() {
		return $this->_requiredKeys;
	}

   /**
    * Check if given variable is set to be required.
    *
	 * If $var is not provided then it will check if any field is required.
    * @param string $var
    * @return bool
    */
   public function isRequired($var = null) {
		if(!$var) return !empty($this->_requiredKeys);
      return in_array($var, $this->_requiredKeys);
   }
   
   /**
    * Checks if any of the required values are missing
    * 
    * @return bool
    */
   public function isMissingRequired() {
      return count($this->_missingRequiredKeys) > 0;
   }

   /**
    * Returns all keys whos values are missing
    * 
    * @return array
    */
   public function getMissingRequired() {
      return $this->_missingRequiredKeys;
   }
   
   /**
    * Get the filterer container for the valation processor
    * 
    * @return Container
    */
   public function getFiltererContainer() {
      if($this->_filters == null) {
         $this->_filters = new FilterContainer();
      }
      
      return $this->_filters;      
   }
   
   /**
    * Alias of getFilterererContainer
    * @see getFiltererContainer
    * @return FilterContainer
    */
   public function getFilterContainer() {
      return $this->getFiltererContainer();
   }
   

   /**
    * Get the Validator container for this validation processor
    * @return \oxide\validation\ValidatorContainer
    */
   public function getValidatorContainer() {
      if($this->_validators == null) {
         $this->_validators = new ValidatorContainer();
      }
      
      return $this->_validators;
   }
   
   /**
    * Get the processor container for the validation process
    * 
    * @return ProcessorContainer
    */
   public function getProcessorContainer() {
      if($this->_processors == null) {
         $this->_processors = new ProcessorContainer();
      }
      
      return $this->_processors;
   }
   
   public function addProcessCallbacks(\Closure $preprocess = null, \Closure $postprocess = null) {
      if($preprocess) $this->_preProcessCallback[] = $preprocess;
      if($postprocess) $this->_postProcessCallback[] = $postprocess;
   }

   
   /**
    * Perform the validation process
    * 
    * This method will perform following processes in the order
    *    - filter values
    *    - validate values
    *    - process values
    * 
    * @param array $values
    * @param \oxide\validation\Result $result
    * @return null|array
    */
   public function process($values, Result &$result = null)  {
      // calls the preprocess callbacks, if any
      if(!empty($this->_preProcessCallback)) {
         $preprocessors = $this->_preProcessCallback;
         foreach($preprocessors as $processor) {
            $processor($values, $result);
         }
         
      }
      // initial setups
      if(!$result) $result = new Result();
      if(!is_array($values)) $values = (array) $values;

      /*
       * perform filters first
       */
      if($this->_filters) {
         $values = $this->_filters->filter($values);
      }
      
      $required = $this->_requiredKeys;

      // check on all required fields
      foreach($required as $name) {
         $value = null;
         if(isset($values[$name])) {
            $value = $values[$name];
            if(!is_array($value)) {
               $value = trim($value);
            } else {
               // this is an array
               // we need to check if this is $_FILE type
               if(isset($value['error'])) {
                  // assuming this is $_FILE type
                  if($value['error'] == UPLOAD_ERR_NO_FILE) {
                     $value = null;
                  }
               }
            }
         }

         if(empty($value)) {
            $result->currentOffset = $name;
            $result->addError('Required.');
            $this->_missingRequiredKeys[] = $name;
         }
      }
      
      // if any of the required fields is missing,
      // do not continue, regardless of $break value
      if(!empty($this->_missingRequiredKeys)) {
         return null;
      }
      
      if($this->_validators) {
         $this->_validators->validate($values, $result);
      }
      
      if($result->isValid()) {
        if($this->_processors) {
           $values = $this->_processors->process($values, $result);
        }
      }
      
      if(!$result->isValid()) {
         return NULL;
      }
      
      // call the post processor callbacks, if any
      if(!empty($this->_postProcessCallback)) {
         $processors = $this->_postProcessCallback;
         foreach($processors as $processor) {
            $processor($values, $result);
         }
      }
      return $values;
   }
}