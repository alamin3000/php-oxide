<?php
namespace oxide\ui\misc;
use oxide\ui\html\Element;

/**
 * 
 */
class ArrayElement extends Element
{
   
   protected 
           $_keyTag = null,
           $_valueTag = null;

   /**
    * 
    * @param string $tag
    * @param array $items
    * @param array $attributes
    */
   public function __construct($tag = 'div', $keyTag = 'span', $valueTag = 'span', array $items = null, array $attributes = null) 
   {
      parent::__construct($tag, null, $attributes);
      $this->_keyTag = $keyTag;
      $this->_valueTag = $valueTag;
      if($items) $this->addItems ($items);
   }
   
   public function addItems(array $items)
   {
      foreach($items as $item) {
         
      }
   }
}