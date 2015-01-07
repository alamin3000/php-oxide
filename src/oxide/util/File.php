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
      $_handle = null,
      $_file = null,
      $_readwrite = false,
      $_mode = null;
   
   const
      MODE_READ = 'r',
      MODE_WRITE = 'w',
      MODE_APPEND = 'a',
      MODE_WRITE_X = 'x';
   
   
   
   public function __construct($file = null, $mode = null) {
      if($file) $this->setFile ($file);
      if($mode) $this->setMode ($mode);
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
   
   public function isWritable() {
      
   }
   
   public function isReadable() {
      
   }
   
   public function isExecutable() {
      
   }
   
   public function open() {
      if(!$this->_handle) {
         $file = $this->getFile();
         $mode = $this->getMode();
         $handle = fopen($file, $mode);
         $this->_handle = $handle;
      }
      
   }
   
   public function close() {
      if($this->_handle) {
         fclose($this->_handle);
      }
   }
   
   
}