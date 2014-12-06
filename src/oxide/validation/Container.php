<?php
namespace oxide\validation;

/**
 * This is a special purpose container class for validation process
 * 
 * Basically it stores processes in 2d associative array.
 * It provides special iterate method that will iterate over all given $values and processes at the same time
 */
class Container
{
   protected 
      $_chain = array();
   
              
   /**
    * Add to the chain
    * 
    * @param mixed $process process to be stored
    * @param mixed $key if not given then this process will be carried out for all values/elements
    */
   public function add($process, $key = 0)
   {      
      if(is_array($key)) {
         foreach ($key as $akey) {
            $this->_chain[$akey][] = $process;
         }
      } else {
         $this->_chain[$key][] = $process;
      }
   }
   
   public function toArray() {
      return $this->_chain;
   }
   
   /**
    * Special method that iterates over the given $values array and all the stored processes
    * 
    * callback method signature function($process, $key, $value, &$break);
    * 
    * @param mixed $values
    * @param function $process_callback
    * @param function $start_callback
    * @param function $end_callback
    */
   public function iterate($keys, $process_callback) 
   {
      if(!$keys) return;
      if(empty($this->_chain)) return;
      
      if(!is_array($keys)) $keys = (array) $keys;
      $break = false;
      
      /*
       * iterate through all values
       * we will
       */
      foreach($keys as $key) {         
         if(isset($this->_chain[$key])) {
            foreach($this->_chain[$key] as $process) {
               $process_callback($process, $key, $break);
               if($break) {
                  break 2;
               }
            }
         }
      }
   }
}