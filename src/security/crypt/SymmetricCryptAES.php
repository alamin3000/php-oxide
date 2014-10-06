<?php


class SymmetricCryptAES {
	private static $secretKey 	= 'oxide';
	private static $IV			= 'c7098adc8d6128b5d4b4f7b2fe7f7f05';
	private static $ALGO			= MCRYPT_RIJNDAEL_128;
	
	public static function encrypt($str) {
		$bIV = pack("H*", self::$IV);
		
		// encrypt
		$bStr = mcrypt_encrypt(
			self::$ALGO,
			self::$secretKey,
			$str,
			MCRYPT_MODE_CBC,
			$bIV
			);
		
		$hStr = bin2hex($bStr);
		return $hStr;	
	}
	
	public static function decrypt($str) {
		$bIV = pack("H*", self::$IV);
		$bStr = pack("H*", $str);
		$str = mcrypt_decrypt(
			self::$ALGO,
			self::$secretKey,
			$bStr,
			MCRYPT_MODE_CBC,
			$bIV
			);
		
		return $str;
	}
}

?>