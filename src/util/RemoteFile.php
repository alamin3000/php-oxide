<?php
namespace oxide\http;


class RemoteFile
{
   protected
      $_url = null,
      $_statusCode = null,
      $_statusMessage = null,
      $_content = null;

   /**
    *
    * @param string $url
    */
   public function __construct($url)
   {
      $this->_url = $url;

      if(!self::validateUrl($url)) {
         throw new \oxide\util\Exception("URL: '$url' is not valid.");
      }
   }

   public function load()
   {
      //$this->loadUsingFile($this->_url);
      $this->loadUsingCurl($this->_url);
   }

   public function getContent()
   {
      return $this->_content;
   }

   public function getStatusCode()
   {
      return $this->_statusCode;
   }

   public function getMessage()
   {
      
   }

   /**
    * load the content of the $url using the builtin fopen method
    * 
    * @param string $url
    */
   protected function loadUsingFile($url)
   {
      $this->_content = @ file_get_contents($url);

      $headers = @ get_headers($url);
      if($headers) {
         $matches = null;
         preg_match('/\d{3}/', $headers[0], $matches);

         $this->_statusCode = $matches[0];
      }
   }

   protected function loadUsingCurl($url)
   {
      $ch = curl_init($url);
      if($ch) {
         curl_setopt($ch, \CURLOPT_HEADER, false); // disable headers
         curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true); // return as string, nto output

         $this->_content = curl_exec($ch);
         $this->_statusCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);

         curl_close($ch);
      }


   }

   public static function validateUrlExists($url)
   {
      
   }

   /**
    * validate well formed url
    * 
    * @param string $url
    * @return bool
    */
   public static function validateUrl($url)
   {
      // validate using php built-in filter functions
      $flags = \FILTER_FLAG_SCHEME_REQUIRED | \FILTER_FLAG_HOST_REQUIRED;
      $valid = filter_var($url, \FILTER_VALIDATE_URL, $flags);

      if(!$valid) return false;

      // validate host
      $parts = parse_url($url);

      return $valid;
      //if($parts)
   }

   public function __toString()
   {
      if(!$this->_content) {
         return '';
      }
      
      return $this->_content;
   }
}