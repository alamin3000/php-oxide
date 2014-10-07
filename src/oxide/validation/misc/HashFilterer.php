<?php
namespace oxide\validation;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HashFilterer
 *
 * @author aahmed753
 */
class HashFilterer implements Filterer
{
   protected 
      $_hash = null,
      $_salt = null;
   
   const 
      HASH_MD5 = 'md5',
      HASH_SHA1 = 'sha1';
   
   public function __construct($hash = self::HASH_MD5, $salt = null)
   {
      $this->_hash = $hash;
      $this->_salt = $salt;
   }
   
   /**
	 *
	 * @param string $value
	 * @return bool
	 */
	public function filter($value)
   {
      if($this->_salt) $value .= $this->_salt;
      if($this->_hash == self::HASH_MD5) return md5 ($value);
		if($this->_hash == self::HASH_SHA1) return sha1 ($value);
	}
}

?>
