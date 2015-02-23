<?php
namespace oxide\app\helper;

class Formatter {
	public function __construct(HelperContainer $container) {
		
   }
   
   public function currency($amount) {
	   return money_format('%.2n', $amount);
   }
   
   public function number($number) {
	   return number_format($number,2);
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