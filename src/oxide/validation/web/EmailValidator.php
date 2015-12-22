<?php
namespace oxide\validation\web;
use oxide\validation\ValidatorAbstract;
use oxide\validation\Result;

/**
 * validate email address format
 * 
 * use filter functions of PECL
 */
class EmailValidator extends ValidatorAbstract  {
	protected
      $_errorMessage = "Invalid Email Address provided.";
	
	public function __construct() {
      parent::__construct();
		// make sure that filter extension is installed
		if(!function_exists('filter_list')) {
			throw new Exception(__CLASS__ . ' requires filter functions in php 5.2 > or PECL');
		}
	}
	
	public function validate($value, Result &$result = null) {
		if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			return $this->_returnResult(false, $result, $value);
		}
		
		return $this->_returnResult(true, $result, $value);
	}
}