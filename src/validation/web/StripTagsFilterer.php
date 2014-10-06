<?php
namespace oxide\validation\web;
use oxide\validation\Filterer;

class StripTagsFilterer implements Filterer {
   protected 
       $_allowtags = null;
   
   public function __construct($allowtags = null) {
      if($allowtags) { $this->_allowtags = $allowtags; }
   }

   public function filter($values) {
      return strip_tags($values, $this->_allowtags);
   }
}