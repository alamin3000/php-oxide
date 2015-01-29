<?php
namespace oxide\validation;

interface Filterer {
   /**
    * Filters given value
    * 
    * @param mixed $values
    * @return mixed returns filtered value, else null
    */
	public function filter($value);
}