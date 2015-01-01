<?php
namespace oxide\data;
use oxide\util\Exception;

/**
 * Database Connection class
 *
 * Connection is based on PDO extension.  This extension must be loaded before using this class.
 *
 * @package oxide
 * @subpackage data
 * @author Alamin Ahmed <aahmed753@gmail.com>
 */
class Connection {
   /**
    *
    * @access protected
    * @var type 
    */
	protected 
		$_pdo = NULL,
		$_config  = array(),
		$_options = array(),
		$_dsn = '';
	
	const 
		OP_EQ				= '=',
		OP_NOT_EQ		= '<>',
		OP_GREATER_EQ	= '>=',
		OP_LESS_EQ		= '<=',
		OP_GREATER 		= '>',
		OP_LESS 			= '<',
		OP_IN				= 'IN',
		OP_AND 			= 'AND',
		OP_OR				= 'OR',
		OP_LIKE			= 'LIKE',
		OP_NULL			= 'NULL',
		OP_NONE			= 'NONE',
		JOIN_LEFT		= 'LEFT JOIN',
		JOIN_RIGHT		= 'RIGHT JOIN',
		JOIN_INNER		= 'INNER JOIN',
		JOIN_OUTER_LEFT= 'LEFT OUTER JOIN',
	   SESSION_KEY_LAST_QUERY_SQL = '__oxide_data_Connection:lastquerysql',
      SESSION_KEY_LAST_QUERY_PARAM = '__oxide_data_Connection:lastqueryparam',
      SESSION_KEY_LAST_QUERY_COUNT = '__oxide_data_Connection:lastquerycount';
	
	/**
	 * construction
	 *
	 * takes an associative array with all required keys with value.
	 * following keys are required:
	 * 	driver: name of the driver.
	 * 	host: server host name or IP address
	 * 	catalog: database catalog name
	 * 	username: login username
	 * 	password: password
	 * all additional options for PDO must be provided separately in second params.
	 * 
	 * @todo fix dsn for other drivers, or put dsn string on ini file
	 * @access public
	 * @param $config array
	 * @param $options array
	 */	
	public function __construct(array $config, array $options = NULL) {      
      // storing the config array locally.
		// _connect() function will use this to perform actual connection
      $this->_config = $config;
		$this->_options = $options;
	}

	/**
	 * returns internal PDO instant
	 * 
    * @access public
	 * @return \PDO
	 */
   public function getPDO() {
      return $this->_pdo;
   }
	
	/**
	 * generate dsn string
	 *
	 * checks for all dsn required config values and generates pdo dsn string
	 * @access private
	 * @return string
	 */
	private function _generateDSN() {
		// get the driver name
      if(!isset($this->_config['driver'])) {
         $driver = 'mysql';
      } else {
         $driver = $this->_config['driver'];
      }
      
      // get the host name
      if(!isset($this->_config['host'])) {
         $host = 'localhost';
      } else {
         $host = $this->_config['host'];
      }
      
      // get the database name
      if(!isset($this->_config['catalog'])) {
         throw new Exception("database catalog is missing");
      }
      
      // generating the dsn string
      return $this->_dsn = "{$driver}:host={$host};dbname={$this->_config['catalog']}";
	}

   /**
    * Performs actual connection to the database.
 	 *
 	 * every other public function calls this function before perfoming action.
	 * this function will check for active connection, if not attempts to connect.
    * @access private
    */
   private function _connect() {
      if($this->_pdo === NULL) {
			$dsn			= $this->_generateDSN();
			$username	= $this->_config['username'];
			$password	= $this->_config['password'];
			$options		= $this->_options;
			$this->_pdo = new \PDO($dsn, $username , $password, $options);

			// we want errors to through exception instead of classic error messages.
			$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			// use our subclass of PDOStatement
			$this->_pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('oxide\data\Statement', array($this)));
      }
   }
	
	/**
	 * sets PDO attributes.
	 *
	 * must use this method before performing any action in order to take advantage of the 
	 * new settings.
	 * @access public
	 * @param $attr int
	 * @param $value mixed
	 * @return bool
	 */
   public function setAttribute($attr, $value) {
   	$this->_connect();
		return $this->_pdo->setAttribute($attr, $value);
   }
   
	/**
	 * prepares an sql statement for executing.
	 *
	 * use of this method is highly recommended.
	 * @access public
	 * @param $sql string
	 * @return Statement
	 */
   public function prepare($sql) {
		$this->_connect();
		return $this->_pdo->prepare($sql);
	}
   
	/**
	 * Perform direct query to database. 
	 *
	 * This method is discourage in favor of prepare/execute methods.
    * 
    * This method can also take sql\Query object. In that case it will use execute
    * method of that object.
	 * @access public
	 * @param $sql
	 * @return Statement
	 */
   public function query($sql) {
		$this->_connect();
		return $this->_pdo->query($sql);
	}
   
	/**
	 * Performs data manupulation query to database.
	 *
	 * This method is discourage in favor of prepare/execute methods.
	 * @access public
	 * @param $sql
	 * @return int
	 */
   public function exec($sql) {
		$this->_connect();
		return $this->_pdo->exec($sql);
	}
	
	   
	/**
	 * Quotes given string.
	 *
	 * Do not use this function when using prepare.  prepare will
	 * do it's quoting and escaping automatically.
    * @access public
	 * @param $str string
	 * @return string
	 */
   public function quote($str) {
		$this->_connect();
		return $this->_pdo->quote($str);
	}

	/**
	 * returns last insert id if found
	 *
	 * use this method after executing INSERT statement.
	 * the table where INSERT is performed, must have primary key column
	 * @return int
	 */
	public function lastInsertId() {
		return $this->_pdo->lastInsertId();
	}

	/**
	 * returns raw PDO error info
	 * 
    * @access public
	 * @return
	 */
	public function errorInfo() {
		return $this->_pdo->errorInfo();
	}
   
   /**
    *
    * @access public
    * @param type $table
    * @param type $param
    * @param type $smnt 
    */
	public function select($table, $param, &$smnt = null) {
		if(array_key_exists('select', $param)) {
         //$select =
      }
	}

	/**
	 * performs insert operation on specified database table
	 * @access public
	 * @param string $table
	 * @param array $row must be an assoicative array 
	 * @return Oxide_Db_Statement
	 */
	public function insert($table, $row, Statement &$smnt = null ) {
		// prepare columns to be inserted
		$columns = implode(',', array_keys($row));
		$values = implode(',', array_fill(0, count($row), '?'));
		
		// reuse if statement is being maintained by the caller
		if(!$smnt) {
			// building the sql query
			$sql = "insert into {$table} ({$columns}) values ({$values})";
		
			// preparing the sql statement
			$smnt = $this->prepare($sql);
		}
		
		// execute
		$smnt->execute(array_values($row));
		
		// return
		return $this->lastInsertId();
	}

	/**
	 * performs update operation on specified database table
	 *
	 * updates a given $table with given $row information based on given $where value.
	 * @access public
	 * @param string $table
	 * @param array $row must be associative array
	 * @param string $where sql where clause portion
	 * @param array $binds additional parameters to be binded for where clause
	 * @return DbStatement
	 */
	public function update($table, $row, $where, array $binds = null, Statement &$smnt = null) {
		if(!is_array($where)) { $where = [$where]; }
      
		// prepare columns to be updated
		foreach($row as $key => $value) {
			/**
			 * for null value, set special sql null keyword
			 * binding param with PARAM_NULL is not working.
			 * workaround is to manually set null keyword and unset the row from the binding
			 */
			if(is_null($value) || strtolower($value) == 'null') {
				$set[] = "$key = null";
				unset($row[$key]);
			} else {
				$set[] = "$key = :{$key}";
			}
		}
		
		if(!$smnt) {
			// build the sql query
			$sql = "update {$table} set " . implode(',', $set) . " where " . implode(' AND ', $where);
		
			// prepare sql statement
			$smnt = $this->prepare($sql);
		}
		
		// execute
		$smnt->execute(array_merge($row, $binds));
	
		// return statement for 
		return $smnt->rowCount();
	}
	
	/**
	 * performs delete operation on specified database table
	 * @access public
	 * @param string $where
	 * @param array $binds
	 * @return Oxide_Db_Statement
	 */
	public function delete($table, $where, array $binds = null, Statement &$smnt = null) {
		// reuse statement if maintained by the caller
		if(!$smnt) {
			// build the sql query
			$sql = "delete from {$table} where {$where}";
		
			// prepare the sql statement
			$smnt = $this->prepare($sql);
		}
		
		// execute
		$smnt->execute($binds);
		
		// return
		return $smnt->rowCount();
	}

	/**
	 * returns an associative array of columns for a give $table.
	 *
	 * @note name of the column is the key of the array and type of the column is the value of the array
	 * @param string $table
	 * @return array
	 */
	public function columnsForTable($table) {
		// performs database query
      $columns = $this->query("SHOW COLUMNS FROM {$table}")->fetchAll();
		$schema = array();
		
      // scan throw all columns
      foreach($columns as $column) {
         // get the field name
         $name = $column['Field'];

         // determine field value type
         // and it's size or values
         if(preg_match("/([\w]+)\(([\w\W]+)\)/", $column['Type'], $matches))  {
            $type = $matches[1];
            $size = str_replace("'",'', $matches[2]);
         } else {
            $type = $column['Type'];
            $size = null;
         }

         $schema[$name] = $type;

      }

      // return the schema
      return $schema;
	}
   	
   /**
    * disconnects from the database
    * @access public 
    */
   public function close() { }

}