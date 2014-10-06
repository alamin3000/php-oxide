<?php
namespace oxide\data\sql;

/**
 * handles sql update query.
 *
 * allows to build and execute an database table update query
 * class uses prepare/exectue methods.
 * @package Oxide
 * @subpackage Db
 * @todo needs optimization and check on execute/set method implementation
 * @author Alamin Ahmed <aahmed753@gmail.com>
 */
class UpdateQuery extends Query {   
   
   /**
    *
    * @access private
    * @var type 
    */
   private $_set = array();

	/**
	 * sets columns to be updated
	 *
	 * must provide associative array
	 * @access public
	 * @param array $row
	 * @return void | string
	 */
   public function set($row = null) {
      if($row) {
         $this->_set = $row;
			$this->param($row);

      } else {
         $sql = '';
			
			$count = count($row);
         foreach($this->_set as $col => $val) {
         	if($val === null || strtoupper($val) == 'NULL') {
         		$sql .= "{$col} = NULL,";
//  					unset($this->_set[$col]);
         		unset($this->_param[$col]);
         		continue;
         	}
         	
				$values[$col] = $val;
            $sql .= "{$col} = :{$col},";
         }
         return rtrim($sql, ',');
      }
   }
   
   
   /**
    *
    * @access public
    * @param type $params
    * @return type 
    */
   public function execute($params = array()) {
         $this->set($params);
      return parent::execute()->rowCount();
   }
   
   
   /**
    *
    * @access public 
    */
   public function reset() {
   	$this->_set  = array();
   	parent::reset();
   }
   
   /**
 	 * render query for sql update
 	 * @access public
	 * @return string
	 */
   public function render($sender = null) {
      $sql = "UPDATE " .
               $this->_table . " " .
               "SET " . $this->set() . " ".
               $this->_whereSql();
      return $sql;
   }
}