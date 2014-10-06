<?php
namespace oxide\validation;

class BooleanValidator extends ValidatorAbstract
{
	private 
		$_errorMessage = '';

	public function __construct($bool) 
	{
      throw new Exception('not implemented yet');
		$this->_bool = $bool;
	}
	
	public function validate($val)
	{
		if(!$this->_bool) {
			$this->_addMessage($this->_failmessage);
			$this->_isValid = false;
		}
		
		$this->_isValid = true;
	}
}
