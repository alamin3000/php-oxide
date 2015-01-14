<?php
namespace oxide\validation;

class ProcessorContainer extends Container implements Processor {   
   
   public 
      /**
       * @var bool indicates if should stop process after first error
       */
      $breakOnFirstError = false;
   
   /**
    * Adds a processor to the container
    * 
    * @param \oxide\validation\Processor $processor
    * @param string $name
    * @return bool
    */
   public function addProcessor(Processor $processor, $name = null) {
      return parent::add($processor, $name);
   }
   
   /**
    * 
    * @param array $values
    * @param \oxide\validation\Result $result
    * @return null|array
    */
   public function process($values, Result &$result = null)  {
      $processed = $values;
      $shouldbreakonfirsterror = $this->breakOnFirstError;
      $this->iterate(array_keys($values), function(Processor $process, $key, &$break) use (&$processed, &$result, &$shouldbreakonfirsterror) {
         // we don't want to perform validation if value is not given
         if(empty($processed[$key])) return;

         // execute process method on each process
         $result->currentOffset = $key;
         $processedvalue = $process->process($processed[$key], $result);
         if(!$result->isValid() && $shouldbreakonfirsterror) {
            $break = true;
         } else {
            $processed[$key] = $processedvalue;
         }
         $result->currentOffset = null;
      });
            
      if(!$result->isValid()) {
         return NULL;
      }
      
      return $processed;
   }
}