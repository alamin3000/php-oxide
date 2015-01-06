<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\util;

class File {
   
   protected
      $_file = null,
      $_mode = null;
   
   const
      MODE_READ = 'r',
      MODE_WRITE = 'w',
      MODE_APPEND = 'a',
      MODE_WRITE_X = 'x';
   
   
   
   public function __construct($file = null, $mode = null) {
//      if($file) 
   }
   
   public function setFile($file) {
      $this->_file = $file;
   }
   
   public function getFile() {
      return $this->_file;
   }
   
   public function setMode($mode) {
      $this->_mode = $mode;
   }
   
   public function getMode() {
      return $this->_mode;
   }
   
}