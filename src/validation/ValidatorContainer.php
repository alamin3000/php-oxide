<?php
namespace oxide\validation;

/**
 * Validator Container
 *
 * used to chain multiple validators and to validate against given associative array
 *
 * @todo check for the required first
 * @todo find out the mystery code in the isValid
 * @todo validators reversed may be overhead. optimize!
 * @todo allow optional index for adding validator
 */
class ValidatorContainer extends Container implements Validator
{
	protected
      $_isValid      = true,
      $_requires     = array(),
      $_missingRequired = array();
   
   public
      $breakOnFirstError  = false;

   /**
    * construction
    */
	public function __construct()
	{
	}
   
   /**
    * Add a validator to the container
    * 
    * @param \oxide\validation\Validator $validator
    * @param string $key
    * @return 
    */
   public function addValidator(Validator $validator, $key)
   {
      return $this->add($validator, $key);
   }
      
	/**
    *
    * @param array $values
    * @param bool $break
    * @return bool
    */
	public function validate($values, ValidationResult &$result = null)
	{
      if(!$result) {
         $result = new ValidatorResult();
      }
      
      $shouldbreakonfirsterror = $this->breakOnFirstError;
      
      $this->iterate(array_keys($values), 
              function($process, $key, &$break) use (&$values, &$result, $shouldbreakonfirsterror) {
         
         // we don't want to perform validation if value is not given
         if(empty($values[$key])) return;
        
         $result->currentOffset = $key;
         $process->validate($values[$key], $result);
         $result->currentOffset = null;
         
         if(!$result->isValid() && $shouldbreakonfirsterror) {
            $break = true;
         }
      });


		if(!$result->isValid()) {
         return NULL;
      }
      
      return $values;
	}
}