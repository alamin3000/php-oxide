<?php
namespace oxide\util;

class ConfigFile implements \ArrayAccess, \Countable {
   use pattern\ArrayFunctionsTrait;

   /**
    * 
    * @param type $files
    * @param type $data
    * @param type $readonly
    */
   public function __construct($file = null, $data = null) {
      if($file) {
	      $this->merge(self::parse($file));
      }
      if($data) {
	      $this->merge($data);
      }
   }   
      
   /**
    * 
    * @param type $file
    * @return type
    * @throws \Exception
    */
   public static function parse($file) {
      // first we need to check if file exits
      if(!is_file($file)) {
         throw new \Exception("File: $file is not found.");
      }
      
      $info = pathinfo($file);
      $data = null;
      switch(strtolower($info['extension'])) {
         case 'json':
            $raw = file_get_contents($file);
            $data =json_decode($raw, true);
            
            break;
         
         case 'ini':
            $data = parse_ini_file($file, true);
            
            break;
            
         case 'php':
         	$data = include $file;
      }
      
      return $data;
   }
}