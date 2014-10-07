<?php
namespace oxide\validation;

/**
 * unix time filter
 *
 * convert given value into php unix time stamp
 * @package oxide
 * @subpackage filter
 * @author Alamin Ahmed <aahmed753@gmail.com>
 */
class UnixTimeFilterer implements Filterer {

   public function filter($value)
   {
      if(is_numeric($value)) return $value;
      return @strtotime($value);
   }
}
?>