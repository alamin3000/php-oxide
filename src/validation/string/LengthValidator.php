<?php
namespace oxide\validation\string;
use oxide\validation\ValidatorAbstract;
use oxide\validation\ValidationResult;

class LengthValidator extends ValidatorAbstract {
	private 
      $_min = null,
      $_max = null;

   /**
    * construction
    * @param int $min
    * @param int $max
    */
	public function __construct($min = null, $max = null) {
		$this->_min = $min;
		$this->_max = $max;
		$this->_errorMessage  = "Value must be between $min and $max characters.";
	}
	
	public function validate($str, ValidationResult &$result = null) {
		// only test for min if min is defined
		if($this->_min !== null) {
			if(strlen($str) < $this->_min) {
				// too short
				return $this->_returnResult(false, $result);
			}
		}
		
		// only test for max if max is defined
		if($this->_max !== null) {
			if(strlen($str) > $this->_max) {
				// too long
				return $this->_returnResult(false, $result);
			}
		}
		
		return $this->_returnResult(true, $result, $str);
	}
}