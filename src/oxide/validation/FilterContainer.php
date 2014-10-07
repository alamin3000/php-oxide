<?php
namespace oxide\validation;

/**
 * A container class for managing filters
 * 
 */
class FilterContainer extends Container implements Filterer
{

   /**
    * add a filter to the container
    * 
    * @param Filterer $filter
    * @param string $name
    */
	public function addFilterer(Filterer $filter, $name = null)
	{
      $this->add($filter, $name);
	}
   
   /**
    * Alias of addFilterer
    * 
    * @see addFilterer
    * @param \oxide\validation\Filterer $filter
    * @param string $name
    */
   public function addFilter(Filterer $filter, $name = null)
   {
      if(is_array($name)) {
         foreach($name as $aname) {
            $this->add($filter, $aname);
         }
      } else {
         $this->add($filter, $name);
      }
      
   }
   
   /**
    *
    * @param type $values
    * @return type mixed
    */
   public function filter($values)
   {
      $filtered = $values;

      $this->iterate(array_keys($values), function($filterer, $key) use (&$filtered) {
         $filtered[$key] = $filterer->filter($filtered[$key]);
      });

      return $filtered;
   }
}