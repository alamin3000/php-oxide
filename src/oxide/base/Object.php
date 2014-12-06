<?php
namespace oxide\base;
use oxide\base\pattern\PropertyAccessTrait;

/**
 * A Generic object
 * Supports readonly properties and modification tracking
 */
class Object 
   extends AbstractClass 
   implements Stringify {
   use PropertyAccessTrait;
   
   public function __construct() {
   }
   
   public function __toString() {
      return "[Object: ". get_called_class() . "]";
   }
}