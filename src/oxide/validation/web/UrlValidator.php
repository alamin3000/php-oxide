<?php
namespace oxide\validation;

class UrlValidator extends ValidatorAbstract
{
	protected 
      $_errorMessage = "Url is invalid",
      $_schema,
      $_query,
      $_flags;

   /**
    * construction
    *
    * @param bool $require_schema forces schema to be required
    * @param bool $require_query forces query string to be included
    */
	public function __construct($require_query = false)
	{
		// make sure that filter extension is installed
		if(!function_exists('filter_list')) {
			throw new Exception(__CLASS__ . ' requires filter functions in php 5.2 > or PECL');
		}
		
		$this->_flags 	|= \FILTER_FLAG_SCHEME_REQUIRED | \FILTER_FLAG_HOST_REQUIRED;
		if($require_query) $this->_flags 	|= \FILTER_FLAG_QUERY_REQUIRED;
	}


   protected function _domainCheck($host)
   {
      return preg_match('/^[^.]+?\.\w{2}/', $host);
   }
	
	public function validate($value, Result &$result = null)
	{
		$valid = (bool)filter_var($value, \FILTER_VALIDATE_URL, $this->_flags);

		return $this->_returnResult($valid, $result, $value);
	}
}