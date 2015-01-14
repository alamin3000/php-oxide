<?php
namespace oxide\validation\db;
use oxide\data\Connection;
use oxide\data\sql\SelectQuery;
use oxide\validation\ValidatorAbstract;
use oxide\validation\Result;

/**
 */
class DbUniqueValueValidator extends ValidatorAbstract {
   protected 
      $_errorMessage = '';

   private
      $_connection = null,
      $_table = null,
      $_field = null,
      $_current_value = '';

   /**
    * 
    * @param Connection $conn
    * @param string $table
    * @param string $field
    * @param string $current_value Current value for the database
    */
   public function __construct(Connection $conn, $table, $field, $current_value = null) {
      $this->_connection = $conn;
      $this->_table = $table;
      $this->_field = $field;
      $this->_current_value = $current_value;
      $this->_errorMessage = "$field must be unique.";
   }

   /**
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return type
    */
   public function validate($value, Result &$result = null) {
      // first check if current value is same as given value
      // in this case we won't check on database
      if($value == $this->_current_value) {
         return $this->_returnResult(true, $result);
      }

      $query = new SelectQuery($this->_table, $this->_connection);
      $query->column('count', 'count('.  $this->_field . ')');
      $query->where($this->_field, (string)$value);
      $count = (int) $query->executeOne();
      
      if($count != 0) {
      	return $this->_returnResult(false, $result, $value);
      }

      return $this->_returnResult(true, $result, $value);
   }
}