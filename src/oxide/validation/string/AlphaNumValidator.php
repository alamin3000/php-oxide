<?php
namespace oxide\validation;

class AlphaNumValidator extends ValidatorAbstract
{
	protected 
      $_allowSpace = false,
      $_unicode = false,
      $_allowchars = null,
      $_errorMessage = "Only letters and numbers are allowed.";

   /**
    * construct the validator
    *
    * need to spacify if white spaces are allowed.
    * @param bool $allowSpace
    * @param bool $unicode
    * @todo white space and unicode needs to be added
    */
	public function __construct($allowSpace = false, $unicode = true, $allowchars = null)
   {
		$this->_allowSpace = $allowSpace;
      $this->_unicode = $unicode;
      $this->_allowchars = $allowchars;
	}
	
	/**
    * check for validity
    * 
    * @param string $value
    * @return bool
    */
	public function validate($value, ValidationResult &$result = null)
   {
      if($this->_allowchars) {
         $chars = $this->_allowchars;
         if(!is_array($chars)) {
            $chars = array($chars);
         }
         
         $value = str_replace($chars, '', $value);
      }
      
      $space = $this->_allowSpace ? '\s' : '';
      if (!$this->_unicode) {
         // POSIX named classes are not supported, use alternative a-zA-Z0-9 match
         $pattern = '/[^a-zA-Z0-9' . $space . ']/';
      } else {
         //The Alphabet means english alphabet.
         $pattern = '/[^a-zA-Z0-9'  . $space . ']/u';
      }

		if($value != preg_replace($pattern, '', (string) $value)) {
         #if(ctype_alnum((string) $value) ){
         return $this->_returnResult(false, $result, $value);
		} else {
         
         return $this->_returnResult(true, $result, $value);
      }
	}
}
