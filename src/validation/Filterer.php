<?php
namespace oxide\validation;

interface Filterer {
   /**
    * Filters given value
    * 
    * @param mixed $values
    * @return mixed returns filtered value
    */
	public function filter($value);
}