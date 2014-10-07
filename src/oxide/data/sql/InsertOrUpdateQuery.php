<?php
namespace oxide\data\sql;


class InsertOrUpdateQuery extends InsertQuery {

   
   /**
    *
    * @access private
    * @var type 
    */
	private $_updateSet = array();
	
   
   /**
    *
    * @access public
    * @param type $set 
    */
	public function updateSet($set) {
		foreach($set as $col) {
			$this->_updateSet[] = "{$col} = VALUES({$col})";
		}
	}
	
   
   /**
    *
    * @access public
    * @return string 
    */
	public function render() {
		$sql = "INSERT INTO " .
		       $this->table . " " .
		       $this->set(). 
		       " ON DUPLICATE KEY UPDATE ".
		       implode(', ', $this->_updateSet);

		return $sql;
	}
	
}