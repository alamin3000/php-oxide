<?php
namespace oxide\validation\file;
use oxide\validation\ValidationResult;
use oxide\validation\Processor;

class MkdirProcessor implements Processor
{
   protected
           $_chmod = 0644;
   
   /**
    * 
    */
   public function __construct($dir)
   {
      ;
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    */
   public function process($value, ValidationResult &$result = null)
   {
      ;
   }
}
