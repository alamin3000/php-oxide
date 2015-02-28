<?php
namespace oxide\data\sql;
use oxide\data\Connection;
use oxide\ui\Renderer;

/**
 * Database Query Object
 * 
 * @package oxide
 * @subpackage data\sql
 */
abstract class Query implements Renderer {
	protected 
      /**
       * @var int Query id
       */
      $_id = null,
      
      /**
       * @var Connection database connection for the query
       */
      $_db = null,
      
      /**
       * @var array Holds data params
       */
      $_param = [],
      
      /**
       * @var string table name for the query
       */
      $_table = null,
      
      /**
       * @var array Where clauses
       */
      $_where = [],
      
      /**
       * @var array Or Where clauses
       */
      $_whereOr = [],
      
      /**
       * @var array Join statements
       */
		$_join = [],
		
      /**
       * @var array Fetch mode options
       */
      $_fetchMode = [];

	protected static
		$_count = 0,
		$_pstmts = array();


	/**
	 * construct the query object
	 *
	 * @access public
	 * @param string $table
 	 * @param Connection $db
	 */
	public function __construct($table = null, Connection $db = null) {
		if($db) $this->connection($db);
		if($table) $this->table($table);

		// generates unique id for the query
		$this->_id = self::$_count++;
	}
	
  /**
    * set/get current shared database connection
    *
    * If no connection is found then it will attempt to retrive shared connection from the data\Connection object
    * @param Connection $conn
    * @return Connection
    */
   public function connection(Connection $conn = null) {
      if($conn != null) {
         $this->_db = $conn;
      }

      return $this->_db;
   }

   /**
    * get/set database table name for the query
    *
    * @param string $table
    * @return string
    */
	public function table($table = null) {
      if($table) $this->_table = $table;
      return $this->_table;
	}

   /**
    * set fetch mode for the database Statement
    * 
    * @access public
    * @param int $mode sets the fetch mode
    * @param mixed $arg1 fetch mode argument
    * @param array $arg2 object construction argument
    */
	public function setFetchMode($mode, $arg1 = null, $arg2 = null) {
		$this->_fetchMode = array($mode, $arg1, $arg2);
	}
		
	/**
	 * Set sql params
	 *
	 * params must be named based placeholders.
	 * all params will be merged if param already exists.
	 * @access public
	 * @param mixed
	 * @return null | array
	 */
   public function param($param = null, $reset = false) {
		if($reset) {
			$this->_param = $param;
		}

		if($param) {
			$this->_param =  array_merge($this->_param, (array) $param);
		}

		return $this->_param;
   }

	/**
	 * Join a table
	 *
	 * you can use following syntax
	 * join('table', 'forieng_id') // this will use USING syntax
	 * join('table', array('forieng_id' => 'primary_id')) // this will use ON syntax.
    * @access public
	 * @param string $table
	 * @param string $join_col
	 * @param string $type
	 * @return void
	 */
   public function join($table, $join_col, $type = Connection::JOIN_INNER) {
      if(is_array($table)) {
         $tkey = key($table);
         $tvalue = current($table);
         $table = "{$tvalue} as {$tkey}";
      }
      
		$this->_join[] = array(
			'type'	=> $type,
			'table'	=> $table,
			'col'		=> $join_col
			);
	}

	/**
    * add a prepared where clause
    *
    * creates a sql where expression and stores in the expression in to the array
    * will create where statement with a placeholder by the name of column (without the schema if presents)
    * only first param $col is required, which is the column used for filter
    * second param $val is value for the column to be filtered by
    * if $val is provided, then value will be binded to the placeholder, else value must be provided later or with explicit bind() or in excecute() method
    * optionaly, if different placeholder is required for the value, then a placeholder (started with colon) can be given in $val param
    * [[cdata]]
    *    // where first_name = :first_name
    *    $Query->where('first_name');
    *
    *    // where member.first_name = :first_name
    *    // also stores value to the bind array
    *    $Query->where('member.first_name', 'James');
    * [[/cdata]]
    * 
    * @access public
    * @param string $col
    * @param string $val
    * @param string $op
    * @return void
    */
   public function where($col, $val = null, $op = Connection::OP_EQ) {
      $this->addWhereClause('AND', $col, $val, $op);
   }

	/**
	 * Add OR operated where clause 
    * 
    * @access public
    * @see where
	 * @param string $col
	 * @param <type> $val
	 * @param <type> $op
	 */
   public function whereOr($col, $val = null, $op = Connection::OP_EQ) {
   	$this->addWhereClause('OR', $col, $val,  $op);
   }

   /**
	 * execute the current and returns executed statement
	 *
	 * uses already prepared statement if available.  This is specially useful
	 * when running multiple queries on same statement
	 * @access public
	 * @param array $param
	 * @return \oxide\data\Statement
	 */
	public function execute($param = array()) {
		$param = $this->param($param);
      $stmt = $this->prepare();
		
		// bind the param values
		foreach($param as $key => $value) {
			$stmt->bindValue($key, $value, $this->valueType($value));
		}
		
		// execute
		$stmt->execute();
		return $stmt;
	}
	
	/**
	 * executes and returns single column
	 * @see execute();
	 */
	public function executeOne($param = array()) {
		$smnt = $this->execute($param);
		return $smnt->fetchColumn();
	}
	
	/**
	 * valueType function.
	 * 
	 * @access public
	 * @param mixed $value
	 * @return void
	 */
	public function valueType($value) {
		if(is_null($value)) {
			return \PDO::PARAM_NULL;	
		} else if(is_bool($value)) {
			return \PDO::PARAM_BOOL;
		} else if(is_int($value)) {
			return \PDO::PARAM_INT;
		} else {
			return \PDO::PARAM_STR;
		}
	}
	
	/**
	 * get the prepared statement.
	 * 
	 * if prepared statement exists, it simply returns it.
	 * @access public
	 * @return \oxide\data\Statement
	 * @todo complete setFetchMode setup
	 */
	public function prepare() {	
		if(isset(self::$_pstmts[$this->_id])) {
			$stmt = self::$_pstmts[$this->_id];
		} else {
			// database must be set before calling this method
			if($this->_db == null) {
				throw new \Exception('Database Connection not defined- ' . __METHOD__);
			}

			// preparing the statement
			$stmt = $this->_db->prepare($this->render());
			
			// set fetch mode if provided.
         $fetchMode = $this->_fetchMode;
         if($fetchMode) {
         	if(!is_null($fetchMode[2])) {
         		$stmt->setFetchMode($fetchMode[0], $fetchMode[1], $fetchMode[2]);
         	} else if(!is_null($fetchMode[1])) {
					$stmt->setFetchMode($fetchMode[0], $fetchMode[1]);
				} else {
               $stmt->setFetchMode($fetchMode[0]);
            }
         }

			// cache the statement for reuse
			self::$_pstmts[$this->_id] = $stmt;
		}
      
		return $stmt;
	}

	/**
	 * returns current statement, if exists
	 *
	 * if this query has been executed, then this statement may be reused.
	 * @return \oxide\data\Statement
	 */
	public function statement() {
		if(isset(self::$_pstmts[$this->_id])) {
			$stmt = self::$_pstmts[$this->_id];
		} else $stmt = null;

		return $stmt;
	}
	
	/**
	 * resets the query
	 * 
	 * every derived class should override this method to clear it's own variables
	 * and call this function i.e parent::reset();
	 * @access public
	 */
	public function reset() {
		if(isset(self::$_pstmts[$this->_id])) {
			self::$_pstmts[$this->_id] = null;
		}
		
		$this->_param  = array();
		$this->_table  = null;
		$this->_where  = array();
	}
	

	/**
	 * quotes string for db safe db query
	 *
	 * code take from Zend Framework, and modified to handle array
	 * @access public
	 * @param mixed $values string to be quoted
	 * @return string safe quoted string
	 */
	public function quote($values) {
      // quote array
      if(is_array($values)) {
			$newvalues = array();
         foreach($values as $value) {
            $newvalues[] = $this->quote($value);
         }
         
         return $newvalues;
      }
      
      // quote single value
      else {
         if(is_int($values) || is_float($values)) {
             return $values;
         } elseif($values === null) {
            return 'NULL';
         } elseif(substr($values, 0,1) == ':') {
				return $values;
			}
         
         return "'" . addcslashes(trim($values), "\000\n\r\\'\"\032") . "'";
      }
	}

	/**
	 * store where statements
	 * @access protected
	 * @todo complete implementation of other operations
	 */
   protected function addWhereClause($type, $col, $val = null, $op = Connection::OP_EQ) {
      if($type == "AND") {
   		$storage = "_where";
   	} elseif($type == "OR") {
   		$storage = "_whereOr";
   	} else {
   		throw new Exception("SQL WHERE TYPE: $type IS NOT RECOGNIZED");
   	}

		// adjust placeholder name. database has issue with (.) within the placeholder
		if(strstr($col, '.')) {
			$placeholder = \str_replace('.', '_', $col);
		} else {
			$placeholder = $col;
		}

		// check if placeholder is passed for value
		// if so use that
      // we need additional string datatype test because IN operator will pass in array.
		if($val && is_string($val) && substr($val, 0, 1) == ':') {
			$placeholder = $val;
			$val = null;
		} else {
			$placeholder = ":{$placeholder}";
		}

   	switch($op) {
   		case Connection::OP_EQ:
			case Connection::OP_NOT_EQ:
			case Connection::OP_GREATER_EQ:
			case Connection::OP_LESS_EQ:
			case Connection::OP_GREATER:
			case Connection::OP_LESS:
				$str = "{$col} {$op} {$placeholder}";
   			$this->{$storage}[] = $str;

   			// store if value is provided
   			if($val !== null) $this->param(array($placeholder => $val));
   			break;

   		case Connection::OP_IN:
   			$this->{$storage}[] = "{$col} IN (". implode(',', $this->quote($val)).")";
				break;

			case Connection::OP_LIKE:
				$val = $this->quote($val); // quote the string
				$val = trim($val, '\''); // remove the outside quote
				$this->{$storage}[] = "{$col} LIKE {$placeholder}";
				$this->param( array( $placeholder => "%{$val}%"));
				break;

			case Connection::OP_NONE:
				$this->{$storage}[] = $col;
				break;
         
         case Connection::OP_NULL:
            $this->{$storage}[] = "{$col} IS NULL";
            break;
   	}
   }

	/**
	 * generate complete where clause statement
	 * @access protected
	 * @return string
	 */
   protected function renderWhere() {
   	// check if where clause is provided
      // if so add it to the statement
      $sql = "";
      $where = "";
      if(count($this->_where) > 0) {
         $where .= "(" . implode(' AND ', $this->_where) .")";
      }

      if(count($this->_whereOr) > 0) {
         $where .= " OR (" . implode(' AND ', $this->_whereOr) . ")";
      }

      if(!empty($where)) {
         $sql .= " WHERE {$where} ";
      }

      return $sql;
   }


	/**
	 *
    * @access protected
	 * @return string
	 */
	protected function renderJoin() {
		// check to see if any joins available
		if(count($this->_join)) {
			$_joinSql = ''; // holds the final join sql statement

			foreach($this->_join as $join) {
				$col = ''; // holds on/using clause

				// if col is array then use ON operation
				// else using USING operation
				if(is_array($join['col'])) {
					$cols = array();
					foreach($join['col'] as $col1 => $col2) {
			         if(is_int($col1)) {
			            $cols[] = "{$col2}";
			         } else {
   						$cols[] = "{$col1} = {$col2}";
   			      }
					}
					$col = "ON " . implode(' AND ', $cols);
				} else {
					$col = "USING ({$join['col']})";
				}

				// building the join clause
				$_joinSql .= " {$join['type']} {$join['table']} {$col} ";
			}

			return $_joinSql;
		} else {
			return '';
		}
	}
	
	/**
    *
    * @access public 
    */
	public static function begin() { throw new \Exception('This method is not implemented yet.') ;}

   /**
    *
    * @access public 
    */
	public static function commit() { throw new \Exception('This method is not implemented yet.') ;}

   /**
    *
    * @access public 
    */
	public static function rollback() { throw new \Exception('This method is not implemented yet.') ;}

	
	/**
	 * allow string representation of the query object.
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
   
   /**
    * Remove the prepared statement for this object if exists
    * @access public
    */
   public function __destruct() {
      if(isset(self::$_pstmts[$this->_id])) {
         unset(self::$_pstmts[$this->_id]);
      }
   }
}