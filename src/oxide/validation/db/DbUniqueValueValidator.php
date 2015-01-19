<?php
namespace oxide\validation\db;
use oxide\data\Connection;
use oxide\data\sql\SelectQuery;
use oxide\validation\ValidatorAbstract;
use oxide\validation\Result;

/**
 */
class DbUniqueValueValidator extends ValidatorAbstract {
   public
      $count = null;
   
   protected 
      $_errorMessage = '';

   private
      $_query = null,
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
   
   public function getQuery() {
      if($this->_query === null) {
         $query = new SelectQuery($this->_table, $this->_connection);
         $query->column('count', 'count('.  $this->_field . ')');
         $query->where($this->_field);
         $this->_query = $query;
      }
      
      return $this->_query;
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

      $query = $this->getQuery();
      $count = (int) $query->executeOne([$this->_field => (string) $value]);
      $this->count = $count;
      if($count != 0) {
      	return $this->_returnResult(false, $result, $value);
      }

      return $this->_returnResult(true, $result, $value);
   }
}