<?php
namespace oxide\data\sql;

/**
 *
 * @package oxide
 * @subpackage data
 */
class DeleteQuery extends Query {

   /**
    *
    * @access public
    * @param type $sender
    * @return type 
    */
	public function render($sender = null) {
		$sql = 	"DELETE {$this->_table}.* FROM " .
					$this->_table . ' ' .
					$this->renderJoin() .
					$this->renderWhere();

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
	public function execute($params = null) {
		$smnt = parent::execute($params);
      return $smnt->rowCount();
	}
}