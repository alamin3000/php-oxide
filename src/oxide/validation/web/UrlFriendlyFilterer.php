<?php
namespace oxide\validation\web;
use oxide\validation\Filterer;

class UrlFriendlyFilterer implements Filterer {
   public function filter($value) {
      $value = strtolower(trim($value));
      $value = strip_tags($value);
      
      /*
       * codes taken from book Practical Web 2.0 ... by Zervaas
       */
      $filters = array(
         '/&+/'   => 'and',         // replace & with 'and'
         '/[^a-z0-9]+/i'   => '-',  // replace non-alpha with hypen
         '/-+/'   => '-'            // replace multiple hypans with single one
         );
      
      // perform filters
      foreach($filters as $regex => $replace) {
         $value = preg_replace($regex, $replace, $value);
      }
         
      // remove hyphens from begingin or end
      $value = trim($value, '-');
      return $value;
   }
}