<?php
namespace oxide\validation;

interface ValidationComponent extends Filterer, Validator {
   /**
    * @return bool
    */
   public function isRequired();
}