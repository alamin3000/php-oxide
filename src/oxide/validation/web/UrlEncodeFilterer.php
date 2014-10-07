<?php
namespace oxide\validation;

/**
 * Description of UrlEncodeFilterer
 *
 * @author aahmed753
 */
class UrlEncodeFilterer implements \oxide\validation\Filterer 
{
   public function filter($value)
   {
      $value = rawurldecode($value);
      return rawurlencode($value);
   }
}

?>
