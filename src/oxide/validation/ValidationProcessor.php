<?php
namespace oxide\validation;
/**
 * 
 */
class ValidationProcessor implements Processor {
   public
      /**
       * @var bool
       */
      $breakOnFirstError = true;
   
   protected
      /**
       * @var FilterContainer Description
       */
      $_filters = [],
           
      /**
       * @var ValidatorContainer Description
       */
      $_validators = [],

      $_requiredKeys = [],
      $_missingRequiredKeys = [];
   
   public function __construct()  {
   }
   
   /**
    * 
    * @param ValidationComponent $component
    * @param type $key
    */
   public function addValidationComponent(ValidationComponent $component, $key) {
      $this->addFilterer($component, $key);
      $this->addValidator($component, $key);
   }
   
   /**
    * 
    * @param Filterer $filterer
    * @param type $keys
    */
   public function addFilterer(Filterer $filterer, $keys) {
      if(is_array($keys)) {
         foreach($keys as $key) {
            $this->_filters[$key][] = $filterer;
         }
      } else {
         $this->_filters[$keys][] = $filterer;
      }
   }
   
   /**
    * 
    * @param Validator $validator
    * @param string|array|null $keys
    */
   public function addValidator(Validator $validator, $keys) {
      if(is_array($keys)) {
         foreach($keys as $key) {
            $this->_validators[$key][] = $validator;
         }
      } else {
         $this->_validators[$keys][] = $validator;
      }
   }

  /**
    * set required variables
    * 
    * @param array $vars names of the variables those are required
    */
   public function setRequiredKeys(array $vars) {
      if(is_array($vars)) {
         $this->_requiredKeys = $vars;
      }
   }

   /**
    * Add key(s) whos value is required
    * 
    * @param mixed $vars can be single key, or arry of keys
    */
	public function addRequiredKeys($vars) {
		if(!is_array($vars)) $vars = [$vars];

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
      $break = $this->breakOnFirstError;
      if(!$result) $result = new Result();
      if(!is_array($values)) $values = [$values];
      $keys = array_keys($values);
      $required = $this->_requiredKeys;
      
      /*
       * perform filters first
       */
      if(!empty($this->_filters)) {
         foreach($keys as $key) {
            if(isset($this->_filters[$key])) {
               foreach($this->_filters[$key] as $filter) {
                  $values[$key] = $filter->filter($values[$key]);
               }
            }
         }
      }
      
      // check on all required fields
      if(!empty($required)) {
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

         if(!empty($this->_missingRequiredKeys)) {
            return null;
         }
      }
      
      /*
       * perform validation
       */
      if(!empty($this->_validators)) {
         foreach($keys as $key) {
            if(isset($this->_validators[$key])) {
               foreach($this->_validators[$key] as $validator) {
                  if(empty($values[$key])) return;
                  $result->currentOffset = $key;
                  $validator->validate($values[$key], $result);
                  $result->currentOffset = null;
                  if(!$result->isValid() && $break) {
                     break 2;
                  }
               }
            }
         }
      }
      
      if(!$result->isValid()) {
         return NULL;
      } else {
         return $values;
      }
   }
}