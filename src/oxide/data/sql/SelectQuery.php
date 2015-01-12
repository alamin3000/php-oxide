<?php
namespace oxide\data\sql;

class SelectQuery extends Query {
	protected 
		$_distinct= false,
		$_columns = [],
		$_limit = [],
		$_order = [],
		$_groups= [];

	const
	   SESSION_KEY_LAST_QUERY_SQL = '__oxide_data_SelectQuery:lastquerysql',
      SESSION_KEY_LAST_QUERY_PARAM = '__oxide_data_SelectQuery:lastqueryparam',
      SESSION_KEY_LAST_QUERY_COUNT = '__oxide_data_SelectQuery:lastquerycount';

   /**
    * Resets all internal data for new query
    * 
    * @access public 
    */
	public function reset() {
		parent::reset();
		$this->_columns = [];
		$this->_join = [];
		$this->_limit = [];
		$this->_order = [];
		$this->_groups = [];
	}
   
	/**
	 * add column(s) to the select list.
	 *
	 * this is alais for columns() method
	 * @access public
	 * @param mixed $columns
	 * @param string $schema
	 */
   public function select($columns, $table = null) {
		if(!is_array($columns)) {
			$columns = array($columns);
		}

		$this->columns($columns, $table);
    
   }

	/**
	 * select distinct record
	 * 
    * @access public
	 * @param bool $bool
	 */
	public function distinct($bool = true)
	{
		$this->_distinct = $bool;
	}
	
   
	/**
	 * same as table() function
	 *
	 * does not allow multiple tables in the from clause
	 * additional tables must be added by join
	 * @access public
	 * @param string $table
	 * @param string $schema
	 */
	public function from($table, $as = null) 
	{
      if($as) {
         $table = "{$table} as $as";
      }
		$this->table($table);
	}
	
	/**
	 * add a single column to the column list
	 *
	 * this method provide a way to include any expression as column
	 * @access public
	 * @param string $name
	 * @param string $expr
	 */
	public function column($name, $expr = null) 
	{
		if($expr) $this->_columns[$name] = "{$expr} as {$name}";
		else $this->_columns[$name] = $name;
	}
	
	/**
	 * adds multiple columns
    * 
    * @access public
	 * @see column
	 * @param array $cols
	 * @param string $table
	 */		
	public function columns($cols = null, $table = null) {
		if(!is_array($cols))
			$cols = (array) $cols;
		
		if($table) {
			$table = "{$table}.";
		}

		foreach($cols as $col ) {
			$this->_columns[$col] = $table . $col;
		}
	}

	/**
	 * add sort order to the query
	 *
    * @access public
	 * @param string $field
	 * @param string $order
	 */
	public function order($field, $order = '') 
	{
		$this->_order[] = array('field' => $field, 'order' => $order);
	}
	
   
   /**
    *
    * @access public
    * @param type $fields 
    */
	public function group($fields) 
	{
		$this->_groups = (array) $fields;
	}
	
   
   /**
    *
    * @access public
    * @param type $offset
    * @param type $count 
    */
   public function limit($offset,$count) 
	{
		$this->_limit['offset'] = (int) $offset;
		$this->_limit['count']	= (int) $count;
	}
	
   
   /**
    *
    * @access public
    * @param type $num
    * @param type $size 
    */
	public function page($num, $size) 
	{
		$offset = max(0, ($num - 1) * $size);
		$this->limit($offset, $size); 
	}

   /**
    * retrive data record from the current query
    * 
    * @access public
    * @return int
    */
	public function retriveRecordCount($cache = true)
	{
		$sql = $this->_renderWithoutLimit();
		$smnt = $this->_db->prepare($sql);
		$smnt->execute($this->_param);
		$count = $smnt->fetchColumn();
		return $count;
	}

   /**
    * retrive data record page count
    *
    * @access public
    * @param int $pagesize
    * @return int
    */
	public function retrivePageCount($pagesize, $cache = true)
	{
		return ceil($this->retriveRecordCount($cache) / max($pagesize, 1));
	}
	
   
   /**
    *
    * @access protected
    * @return type 
    */
	protected function _renderWithoutLimit()
	{
	   $sql = "SELECT count(*) as count ";
      $sql .= $this->_fromSql();
		$sql .= $this->_joinSql();
		$sql .= $this->_whereSql();
		$sql .= $this->_groupSql();
		$sql .= $this->_orderSql();
		
   	return $sql;
	}
	
	
   
   /**
    *
    * @access protected
    * @return type 
    */
	protected function _fromSql()
	{
		return 'FROM ' . implode(', ', (array) $this->_table) . ' ';
	}
	
   
   /**
    *
    * @access protected
    * @return type 
    */
	protected function _selectSql()
	{
		if($this->_distinct) {
			$distinct = "DISTINCT ";
		} else {
			$distinct = "";
		}
		return 'SELECT ' . $distinct. implode(', ', array_values($this->_columns)) . ' ';
	}
	
   
   /**
    *
    * @access protected
    * @return string 
    */
	protected function _groupSql()
	{
		if($this->_groups)
			return 'GROUP BY ' . implode(', ', $this->_groups) . ' ';
		else
			return '';
	}
	
   
   /**
    *
    * @access protected
    * @return string 
    */
	protected function _orderSql()
	{
		$sql = '';
		if(count($this->_order) > 0) {
			$_order = '';
			foreach($this->_order as $order) {
				$_order .= "{$order['field']} {$order['order']},";
			}
         
         $_order = rtrim($_order, ',');
         
			$sql = ' ORDER BY ' . $_order . ' ';
		}
		
		return $sql;
	}
	
   
   /**
    *
    * @access protected
    * @return string 
    */
	protected function _limitSql()
	{
		if(count($this->_limit) > 0) {
			return "LIMIT {$this->_limit['offset']}, {$this->_limit['count']} ";
		}
		return '';
	}
	
	/**
	 * string representation of the query
	 * @access public
	 * @return string
	 */
   public function render($sender = null)
	{
      $sql = $this->_selectSql();

      // from table
      $sql .= $this->_fromSql();

      // check to see if any joins available
		$sql .= $this->_joinSql();
		
		// add where clause
		$sql .= $this->_whereSql();
		
		// check for group by clause
		$sql .= $this->_groupSql();
		
      // adding the order clause
		$sql .= $this->_orderSql();
		
 		// adding limit when available
		$sql .= $this->_limitSql();
		
      // finally return the sql statement
		//dump($sql);
		#dump($this->param());
      return trim($sql);
   }
}