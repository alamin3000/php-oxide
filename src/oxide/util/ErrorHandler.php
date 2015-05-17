<?php
namespace oxide\util;

class ErrorHandler {
	
	public function __construct() {
		
	}
	
	public function register() {
		set_error_handler([$this, 'handler']);
	}
	
	public function handler($errno, $errstr, $errfile, $errline) {
		throw new \Exception("Error type: {$errno} raised: \"{$errstr}\" at {$errfile}:{$errline}");
	}
}