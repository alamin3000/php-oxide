<?php
namespace oxide\validation;


class InputFilterFilterer implements Filterer
{
   protected 
           $_type = null,
           $_flag = null;
   
   public function __construct($type, $flag = null)
   {
      ;
   }
}