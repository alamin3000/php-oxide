<?php
namespace oxide\app\helper;

class Formatter {
	public function __construct(HelperContainer $container) {
		
   }
   
   
	/**
	 * dateFormat function.
	 * 
	 * @access public
	 * @param mixed $date
	 * @param mixed $format (default: null)
	 * @return void
	 */
	public function dateFormat($date, $format = null, $default = 'n/a') {
		if(!$date) return $default;
		
		if(is_string($date)) {
			$date = strtotime($date);
		}
		
		if($date < 0) {
			return $default;
		}
		
		if(!$format) {
			$format = 'Y-m-d';
		}
		
		return date($format, $date);
	}
}