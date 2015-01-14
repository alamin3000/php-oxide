<?php
/**
 * InputValidation class file
 *
 * @package oxide
 */

namespace oxide\validation;

/**
 * InputValidation
 *
 * Input validation is a special class that can be used to validate and parse
 * all user input before using them or storing into database
 *
 * Input validation class manages validators and filters
 * @todo must optimize the $values array so that it doesn't not create multiple copies
 * @package oxide
 * @subpackage validation
 */
class InputValidation
{
   protected
      $_values = null,
      $_validator = null,
      $_preFilterer = null,
      $_postFilterer = null,
      $_inputType = null;

   const
      INPUT_GET = 1,
      INPUT_POST = 2,
      INPUT_USER = 3;

   /**
    * Construct input validation
    *
    * must provide input type {get, post}
    * @param int $inputType
    */
   public function  __construct(&$values = array())
   {
      $this->_validator = new ValidatorContainer();
      $this->_preFilterer = new FilterContainer();
      $this->_postFilterer = new FilterContainer();      
      if($values) $this->setValues ($values);
   }
   
   public function setValues(&$values)
   {
      $this->_values = $values;
   }
	
   /**
    * Returns the validator container
	 * 
    * @return ValidatorContainer
    */
   public function getValidatorContainer()
   {
      return $this->_validator;
   }
   
   /**
    * get the pre filter container
    * 
    * @return FilterContainer
    */
   public function getPreFilterContainer()
   {
      return $this->_preFilterer;
   }
   
   /**
    * get the post filter container
    * 
    * @return FilterContainer
    */
   public function getPostFilterContainer()
   {
      return $this->_postFilterer;
   }

   /**
    * Add a filter to the pre filter list.
    *
    * These filters will be processed before validation is performed
    * @param Filterer $filter
    * @param string $name[optional] if provided then filter will be added to $name value
    */
   public function addPreFilter(Filterer $filter, $name = null)
   {
      $this->_preFilterer->addFilter($filter, $name);
   }

   /**
    * Add a filter to post filter list.
    *
    * These filters will be processed after validation if performed
    * @param Filterer $filter
    * @param string $name[optional] if provided then filter will be added to $name value
    */
   public function addPostFilter(Filterer $filter, $name = null)
   {
      $this->_postFilterer->addFilter($filter, $name);
   }
   
  
   /**
    * Process the input.
    *
    * first the processor will validate data.
    * If validation fails, errors will be populated in $result AND null will return
    * If validation passes, then filtered data will be returned
    * @param ValidatorResult $result
    * @return array
	 * @todo analyze the return values for the failed process
    */
   public function process(Result &$result = null)
   {
      // first start with validating data
      if(!$result) $result = new ValidatorResult();

      // get the values
      $raw_values = $this->getRawValues();
      
      // perform pre filters
      $values = $this->_preFilterer->filter($raw_values);

      // perform validation
      $this->_validator->validate($values, $result);

      // check if validation passed
      if(!$result->isValid()) {
         // if validation fails,
			// we will return NULL, indicating processing failed
         return NULL;
      }

      // start with filtering and return
      return $this->_postFilterer->filter($values);
   }

   /**
    * Returns raw values.
    *
    * Values will be return based on input type
    * @return array will be passed by reference
    */
   public function &getRawValues()
   {
		return $this->_values;
   }
   
   /**
    * 
    * @param  $values 
    */
   public function setRawValues(&$values){
      $this->_values = $values;
   }

	/**
	 * Raw value for the given $key
	 *
	 * Raw values are original values provided and which has not been filtered.
	 * @param string $key
	 * @param string $default[optional]
	 * @return string
	 */
	public function getRawValue($key, $default = null)
	{
		if(isset($this->_values[$key])) {
			return $this->_values[$key];
		}

		return $default;
	}

   /**
    * Provides magic function for easy access to various local objects
    * 
    * @param string $method
    * @param string $arg
    * @return mixed
    */
   public function __call($method, $arg) {
      if(method_exists($this->_validator, $method)) {
			return \call_user_func_array(array($this->_validator, $method), $arg);
      } else {
         throw new \Exception("method: '{$method}' doesnot exist");
      }
   }
}