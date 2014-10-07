<?php
namespace oxide\data\sql;

/**
 * Insert Query Object
 *
 * If value is strictly null or string to NULL
 * @package default
 * @author Alamin Ahmed
 * @todo need revision and otpimization
 */
class InsertQuery extends Query {
	protected $_set = array();
	protected $_ignore = false;
	
   
   /**
    *
    * @access public
    * @param type $bool 
    */
	public function setIgnoreMode($bool)
	{
      $this->_ignore = $bool;	
	}
	
	/**
	 * sets a row of array to be inserted
	 * @access public
	 * @param array $row
	 */
   
   /**
    *
    * @access public
    * @param type $row
    * @return string 
    */
   public function set($row = null) {
      if($row) {
			$this->_set = $row;
			$this->bind($row);
      } else {
			
			// prepare set
			$placeholders = array();
			$keys = array();
			foreach($this->_set as $key => $value) {
				if(is_null($value)) {
					unset($this->_set[$key]);
					unset($this->_param[$key]);
					continue;
				}
				$placeholders[] = ":{$key}";
				$keys[] = "`{$key}`";
			}
			
			// build sql query
			$sql  = ' (' . implode(', ', $keys) . ') ';
			$sql .= 'values ('. implode(', ', $placeholders) .') ';
			
			return $sql;
      }
   }

   
   
/**
 *
 * @access public
 * @param type $sender
 * @return string 
 */	
   public function render($sender = null) {
		/*
		make sure multiple table is not allowed
		*/
   	if($this->_ignore) {
   		$ignore = "IGNORE";
   	} else {
   		$ignore = "";
   	}
      $sql = "INSERT {$ignore} INTO " . 
                  $this->table() . " " .
                  $this->set();
                  		
      return $sql;
   }
	
	/**
	 * perform database insert query and returns the insert id
	 *
	 * @access public
	 * @return int last insert id
	 * @todo column and table quote for database
	 * @todo verify if array is associative or vector
	 */
   
   /**
    *
    * @access public
    * @param type $params
    * @return type 
    */
	public function execute($params = null) {
		// if param is provided, then set the values
		// this is useful to run multiple execution on single prepared statement
		if($params !== null) {
		    $this->set($params);
		}
		parent::execute();
      return $this->_db->lastInsertId();
	}
}