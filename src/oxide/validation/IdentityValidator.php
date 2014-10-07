<?php
namespace oxide\validation;

class IdentityValidator extends ValidatorAbstract
{
	private $_auth;
	public function __construct(\Zend_Auth $auth)
	{
		$this->_auth = $auth;
	}

	public function validate($value, ValidatorResult &$result = null)
	{
		if( $this->_auth->hasIdentity()) {
         return $this->_returnResult(true, $result, $value);
		} else {
         return $this->_returnResult(false, $result, $value);
      }
	}
}