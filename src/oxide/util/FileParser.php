<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;

/**
 * File Parser
 */
class FileParser {
   protected
      $_parsers = [];
   
   /**
    * 
    */
   public function __construct() {
      // add the json parser
      $this->addParser('json', function($file) {
         $raw = file_get_contents($file);
         $data =json_decode($raw, true);
         
         return $data;
      });
      
      // add php file parser
      $this->addParser('php', function($file) {
         return include $file;
      });
         
      // add ini parser
      $this->addParser('ini', function($file) {
         return parse_ini_file($file, true);
      });
   }
   
   /**
    * Add a parser for file $ext
    * 
    * @param string $ext
    * @param \Closure $parser
    */
   public function addParser($ext, \Closure $parser) {
      $this->_parsers[strtolower($ext)] = $parser;
   }
   
   /**
    * Get the parser for file extension
    * 
    * @param string $ext
    * @return \Closure
    */
   public function getParser($ext) {
      $lext = strtolower($ext);
      if(isset($this->_parsers[$lext])) {
         return $this->_parsers[$lext];
      }
      
      return null;
   }
   
   /**
    * Parse the given file
    * 
    * @param string $file 
    * @return mixed
    * @throws \Exception
    */
   public function parse($file) {
      // first we need to check if file exits
      if(!is_file($file)) {
         throw new \Exception("File: $file is not found.");
      }
      
      $info = pathinfo($file);
      $data = null;
      $ext = strtolower($info['extension']);
      
      $parser = $this->getParser($ext);
      if($parser) {
         $data = $parser($file);
         
      }
      
      return $data;
   }
}