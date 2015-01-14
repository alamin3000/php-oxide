<?php
namespace oxide\validation\file;
use oxide\validation\Result;
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
    * @param \oxide\validation\Result $result
    */
   public function process($value, Result &$result = null)
   {
      ;
   }
}
