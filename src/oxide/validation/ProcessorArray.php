<?php
namespace oxide\validation;
use oxide\base\Container;

class ProcessorArray extends Container implements Processor
{
   public 
           $breakOnFirstError = false;
   
   public function process($value, ValidationResult &$result = null)
   {
      // we don't want to perform validation if value is not given      
      if(empty($value)) return $value;
      
      $this->iterate(function(Processor $process, $key, &$break) use (&$value, &$result) {
         // execute process method on each process
         $processedvalue = $process->process($value, $result);
         if(!$result->isValid() && $this->breakOnFirstError) {
            $break = true;
         } else {
            // only change the value if validation passed
            $value = $processedvalue;
         }
      });
            
      
      if(!$result->isValid()) {
         return NULL;
      }
      
      return $value;
   }      
}
