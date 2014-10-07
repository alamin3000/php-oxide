<?php
namespace oxide\data\sql;

/**
 *
 * @package oxide
 * @subpackage data
 */
class DeleteQuery extends Query
{

   /**
    *
    * @access public
    * @param type $sender
    * @return type 
    */
	public function render($sender = null)
	{
		$join_sql = $this->_joinSql();


		if($join_sql) {
			// generate the join tables
//			$join_table = array($this->_table);
//			foreach($this->_join as $join) {
//				$join_table[] = $join['table'];
//			}
//
//			$join_table_sql = implode(',', $join_table);

		}
      
		$sql = "DELETE {$this->_table}.* FROM ";


		$sql .= $this->_table;
		
      // check to see if any joins available
		$sql .= $this->_joinSql();

		// add where clause
		$sql .= $this->_whereSql();

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
		$smnt = parent::execute($params);
      return $smnt->rowCount();
	}
}