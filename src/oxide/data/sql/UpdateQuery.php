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
	 * sets columns to be updated.
	 *
	 * This is different then param() in a sense that you can use to update mutliple information
	 * Without affecting WHERE clause params. ?@#
	 * must provide associative array
	 * @see param
	 * @access public
	 * @param array $row
	 * @return void | string
	 */
   public function set($row = null) {
      if($row) {
         $this->_set = $row;
			$this->param($row);

      }
   }
   
   /**
    *
    * @access public
    * @param type $params
    * @return type 
    */
   public function execute($params = []) {
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
    * Renders the SET clause for the update statement.
    * 
    * @access public
    * @return void
    */
   public function renderSet() {
      $sql = '';
		
      foreach($this->_set as $col => $val) {
			$values[$col] = $val;
         $sql .= "{$col} = :{$col},";
      }
      return "SET " . rtrim($sql, ',');
   }
   
   /**
 	 * render query for sql update
 	 * @access public
	 * @return string
	 */
   public function render($sender = null) {
      $sql = "UPDATE " .
               $this->_table . " " .
               $this->renderSet() . " ".
               $this->renderWhere();
      return $sql;
   }
}