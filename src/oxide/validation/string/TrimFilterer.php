<?php
namespace oxide\validation;

/**
 * Trim filter
 *
 * ablity to trim 
 */
class TrimFilterer implements Filterer
{
   private $_chars = '';

   /**
    * construction
    *
    * you can supply additional charecters to be trimmed instead of the default.
    *
    * @param string $chars
    * @param string $default these are default charecters
    */
   public function  __construct($chars = '', $default = " \t\n\r\0\x0B")
   {
      $this->_chars = $chars . $default;
   }

	/**
	 *
	 * @param string $value
	 * @return bool
	 */
	public function filter($value)
   {
		return trim($value, $this->_chars);
	}
}