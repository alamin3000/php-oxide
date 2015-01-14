<?php
namespace oxide\validation;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
interface Processor 
{

   /**
    * Performs some process on the given $value.
    * 
    * 
    * @param mixed $value value to be processed.
    * @param 
    */
   public function process($value, Result &$result = null);
}
?>
