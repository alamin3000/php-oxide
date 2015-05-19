<?php
namespace oxide\app\helper;

class Formatter {
	public function __construct(HelperContainer $container) {
		
   }
   
   /**
    * Format currency.
    * 
    * @access public
    * @param mixed $amount
    * @return void
    */
   public function currency($amount) {
	   return money_format('%.2n', $amount);
   }
   
   
   /**
    * Format number.
    * 
    * @access public
    * @param mixed $number
    * @return void
    */
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
   
   
   /**
    * 
    * @param type $ptime
    * @return string
    */
   public function timeToString($ptime) {
      $etime = time() - $ptime;

      if ($etime < 1) {
          return '0 seconds';
      }

      $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                  30 * 24 * 60 * 60       =>  'month',
                  7  * 24 * 60 * 60       => 'week',
                  24 * 60 * 60            =>  'day',
                  60 * 60                 =>  'hour',
                  60                      =>  'minute',
                  1                       =>  'second'
                  );

      foreach ($a as $secs => $str) {
          $d = $etime / $secs;
          if ($d >= 1) {
              $r = round($d);
              return $r . ' ' . $str . ($r > 1 ? 's' : '');
          }
      }
  }
  
  /**
    * Convert time to MySQL data/time string format
    * @param int|string $time
    * @return string
    */
   public function toMySqlDateTime($time) {
      if(!is_int($time)) $time = strtotime($time);
      return date("Y-m-d H:i:s", $time);
   }
}