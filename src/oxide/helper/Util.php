<?php
namespace oxide\helper;


abstract class Util {
   
   public static function require_keys($from, $keys) {
      if(!is_array($keys)) {
         $keys = [$keys];
      }
      
      foreach($keys as $key) {
         if(!isset($from[$key])) {
            
         }
      }
   }
   
   /**
    * gets value from the given $from storage using dot path syntax to dig deep
    * @param type $from
    * @param type $keypath
    * @param type $default
    * @return type
    */
   public static function deep($from, $keypath = null, $default = null, $required = false) {
   	if($keypath === null) return $from;
   
      if(!is_array($keypath)) {
      	$keys = explode('.', $keypath);
      } else {
	      $keys = $keypath;
      }
      
      $var = null;
      if(!empty($keys)) {
         $var = $from;
         foreach($keys as $key) {
            if(is_object($var)) {
               if(isset($var->$key)) {
                  $var = $var->$key;
               } else {
                  return $default;
               }               
            } else if(is_array($var)) {
               if(isset($var[$key])) {
                  $var = $var[$key];
               } else {
	               if($required) {
			        		throw new \Exception("Key: $key is required.");
		        		}
                  return $default;
               }
            } else {
               // $key can't be accessed
               // becuase it is not object or array
               // in this case $key is not found
               return $default;
            }
         }
      }
      
      return $var;
   }           
           
   
   /**
   * Attempt to read value from given $storage.  If not found, $default value is passed back.
    * 
   * If key is not provided, the $storage is passed back.
   * @param array|stdClass $storage
   * @param string $key
   * @param mixed $default
   * @param boolean $required 
   * @return mixed
   */
   public static function value($storage, $key = null, $default = null, $required = false) {
     if($key !== null) {
        if(isset($storage[$key])) {
           return $storage[$key];
        } else if(isset($storage->$key)) {
           return $storage->$key;
        } else {
        		if($required) {
	        		throw new \Exception("Key: $key is required.");
        		}
           return $default;
        }
     } else {
        return $storage;
     }
   }
   
   public static function get($storage, $keypath = nul, $default = null, $required = false) {
      
   }
   
   
   public static function set(&$storage, $keypath, $value = null) {
      if(!is_array($keypath)) {
      	$keys = explode('.', $keypath);
      } else {
	      $keys = $keypath;
      }
      
      $var = &$storage;
      $tmp = &$var;
      foreach($keys as $key) {
         $tmp = &$var;
         if(is_object($var)) {
            if(isset($var->$key)) {
               $var = &$var->$key;
            }
         } else if(is_array($var)) {
            if(isset($var[$key])) {
               $var = &$var[$key];
            }
         } else {
            $var = &$tmp;
         }
      }
   }
   
   public static function dateString($time) {
      return date("F j, Y \a\\t h:i a", strtotime($time));
   }
   
   /**
    * 
    * @param type $ptime
    * @return string
    */
   public static function timeToString($ptime) {
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
}