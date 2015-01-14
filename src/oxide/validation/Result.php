<?php
namespace oxide\validation;

/**
 * Result class
 *
 * A validation result container for storing error messages.
 * isValid() method is used to check if result has any error messages, hence validation fail 
 * @package oxide
 * @subpackage validation
 */
class Result
{
   protected
      $_errors = array();

   public
		/**
		 * Sets current offset mark for array.
		 *
		 * This is specially useful when passing around the object between
		 * different processes.  This way other object can add messages to the
		 * proper array offset.
		 * @var string
		 */
		$currentOffset = null;
   

   /**
    * constrct the result
    * 
    * @param string|array $error
    */
   public function __construct($errors = null)
   {
      if(!is_null($errors)) {
         $this->addErrors($errors);
      }
   }

   /**
    * Checks if the result is valid.
    *
    * @param bool $strict
    * @return bool
    */
   public function isValid()
   {
      if(count($this->_errors) > 0) {     
         return false;
      } else {
         return true;
      }
   }

   /**
    * Returns all current error messages for the result set
    * 
    * @return array
    */
   public function getErrors()
   {
      return $this->_errors;
   }
   
   /**
    * Add error messages using associative array
    * 
    * array key represents offset and value represents error message
    * @param array $arr
    */
   public function addErrors(array $arr)
   {
      if(!is_array($arr)) {
			$arr = array($arr);
		}

		foreach($arr as $offset => $str) {
			if(is_int($offset)) {
				$this->addError($str, null);
			} else {
				$this->addError($str, $offset);
			}
		}
   }
   
   /**
    * Add an error message to this result object
    * 
    * if offset is given, then error message will be set for that offset
    * @param string $err error message
    * @param mixed $offset where error message should be set to
    */
   public function addError($err, $offset = null)
   {
      if($offset) {
         $this->_errors[$offset][] = $err;
      } else {
         if($this->currentOffset) {
            $this->_errors[$this->currentOffset][] = $err;
         } else {
            $this->_errors[] = $err;
         }
      }
   }
   
   /**
    * Gets an error message for given offset
    * 
    * @param type $offset
    * @return null
    */
   public function getError($offset)
   {
      if(isset($this->_errors[$offset]))         
         return $this->_errors[$offset];
      else         
         return null;
   }
}