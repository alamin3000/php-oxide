<?php
namespace oxide\validation;

/**
 * 
 */
class UriExistsValidator extends ValidatorAbstract
{
   protected
      $_defaultserver = null,
      $_errorMessage = "URI does not exist.";

   
   /**
    * 
    * @param type $defaulthost if path is relative, the default host will be used
    * @throws \oxide\util\Exception
    */
   public function __construct($defaulthost = null)
   {
      parent::__construct();
      
      if(!function_exists('curl_init')) {
         throw new \oxide\util\Exception("UriExistsValidator requires curl extension");
      }
      
      $this->_defaultserver = $defaulthost;
   }

   /**
    *
    * @param string $url
    * @param ValidatorResult $result
    * @todo php header solution needs revision
    * @author found online
    */
   function validate($url, Result &$result = null) 
   {
      $uri_parts = parse_url($url);
      if(empty($uri_parts['host'])) {
         $url = "{$this->_defaultserver}{$url}";
      }
      
      // curl solution
      // Initialize the handle
      $ch = curl_init();
      // Set the URL to be executed
      curl_setopt($ch, CURLOPT_URL, $url);
      // Set the curl option to include the header in the output
      curl_setopt($ch, CURLOPT_HEADER, false);
      // Set the curl option NOT to output the body content
      curl_setopt($ch, CURLOPT_NOBODY, true);
      /* Set to TRUE to return the transfer
      as a string of the return value of curl_exec(),
      instead of outputting it out directly */
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Execute it
      $data = curl_exec($ch);
      // Finally close the handle

      $status_code = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
      curl_close($ch);

      if($status_code >= 200 && $status_code < 300) {
         $valid = true;
      }
      else {
         $valid = false;
      }
  
      $this->_returnResult($valid, $result);
   }
}