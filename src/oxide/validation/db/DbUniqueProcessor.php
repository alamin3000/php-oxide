<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\validation\db;
use oxide\validation\Processor;
use oxide\validation\db\DbUniqueValueValidator;
use oxide\data\Connection;
use oxide\validation\Result;

class DbUniqueProcessor implements Processor {
   protected
      $_conn = null,
      $_table = null,
      $_column = null,
      $_currentvalue = null;
   
   /**
    * 
    * @param Connection $conn
    * @param type $table
    * @param type $column
    * @param type $currentvalue
    */
   public function __construct(Connection $conn, $table, $column, $currentvalue = null) {
      $this->_conn = $conn;
      $this->_table = $table;
      $this->_column = $column;
      $this->_currentvalue = $currentvalue;
   }
   
   /**
    * 
    * @param type $digits
    * @return type
    */
   public function random($digits = 5) {
      return str_pad(mt_rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
   }
   
   public function uid($prefix = '') {
      return uniqid($prefix);
   }
   

   /**
    * 
    * @param type $val
    * @return type
    */
   public function uniquify($val) {
      $validator = new DbUniqueValueValidator($this->_conn, $this->_table, $this->_column);
      $digit = 3;
      
      rndcheck:
      if(!$validator->validate($val)) {
         $val .= $this->random($digit);
         $digit++;
         goto rndcheck;
      } else {
         return $val;
      }
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return type
    */
   public function process($value, Result &$result = null) {
      if($value == $this->_currentvalue) {
         return $value;
      }
      
      return $this->uniquify($value);
   }
}