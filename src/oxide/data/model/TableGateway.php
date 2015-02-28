<?php
namespace oxide\data\model;
use oxide\data\Connection;
use oxide\data\Statement;
use oxide\data\sql;

class TableGateway {
	protected
		$_strict = false,
		$_rowClass = '\oxide\data\model\DataObject',
		$_schema = null,
		$_table = null,
		$_pks = null,
		$_conn = null;
	
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param Connection $conn
	 * @param string $table
	 * @param string|array $pks
	 * @param null|array $schema (default: null)
	 * @return void
	 */
	public function __construct(Connection $conn, $table, $pks, $schema = null) {
		$this->_conn = $conn;
		$this->_table = $table;
		
		if(is_array($pks)) $this->_pks = $pks;
		else $this->_pks = [$pks];
		
		if($schema) {
			$this->_schema = $schema;
		}
		
		$this->onInit();
	}
	
	/**
	 * Get/Set current strict mode.
	 * 
	 * When strict mode is enabled, access to undefined keys will throw an exeption
	 * @access public
	 * @param mixed $bool (default: null)
	 * @return void
	 */
	public function strict($bool = null) {
		if($bool === null) return $this->_strict;
		else $this->_strict = $bool;
	}
	
	
	/**
	 * Attempt to discover database table schema.
	 * 
	 * Uses INFORMATION_SCHEMA.COLUMNS for getting information about columns
	 * @access public
	 * @return void
	 */
	public function discoverSchema() {
		$conn = $this->getConnection();
		$catalog = $conn->getCatalogName();
		$table = $this->getTableName();
		$query = new sql\SelectQuery('INFORMATION_SCHEMA.COLUMNS', $conn);
		$query->select([
			'column_default', 'column_name', 'data_type', 'character_maximum_length', 'extra'
		]);
		$query->where('table_name', $table);
		$query->where('table_schema', $catalog);
		
		$stmt = $query->execute();
		$columns = $stmt->fetchAll();
		$schema = [];
		if(count($columns)) {
			foreach($columns as $column) {
				$schema[$column['column_name']] = [
					'type' => $column['data_type'],
					'default' => $column['column_default'],
					'length' => $column['character_maximum_length']
				];
			}
		} else {
			throw new \Exception('Unable to find columns information from database.');
		}
		
		return $schema;
	}
	
	/**
	 * Set the schema of the db table
	 * 
	 * @access public
	 * @param array $schema
	 * @return void
	 */
	public function setSchema(array $schema) {
		$this->_schema = $schema;
	}
	
	/**
	 * Get current db table schema
	 * 
	 * @access public
	 * @return void
	 */
	public function getSchema() {
		return $this->_schema;
	}
	
	
	/**
	 * Get the current Database Connection.
	 * 
	 * @access public
	 * @return void
	 */
	public function getConnection() {
		return $this->_conn;
	}
	
	/**
	 * Sets database connection
	 * 
	 * @access public
	 * @param Connection $conn
	 * @return void
	 */
	public function setConnection(Connection $conn) {
		$this->_conn = $conn;
	}
	
	/**
	 * Get the database table name for the model
	 * 
	 * @access public
	 * @return void
	 */
	public function getTableName() {
		return $this->_table;
	}
	
	/**
	 * Set the row class.
	 * 
	 * Complete resolvable class name must be provided,
	 * including namespaces
	 * @access public
	 * @param mixed $class
	 * @return void
	 */
	public function setRowClass($class) {
		$this->_rowClass = $class;
	}
	
	
	/**
	 * Get the current row class function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getRowClass() {
		return $this->_rowClass;
	}
	
	
	/**
	 * Creates an empty data object.
	 * 
	 * Please note, this does not insert/update data into database
	 * You will need to perform save operation
	 * @access public
	 * @param array $row
	 * @return void
	 */
	public function create(array $row = null) {
		if($this->_rowClass) {
			$class = $this->_rowClass;
			
			// filter out the data based on schema, if available
			$data = $this->filterSchemaData($row);
			return new $class($data);
		}
	}
	
	/**
	 * Find a record object using $pkvalue
	 * 
	 * Will use primary key feild to do the lookup
	 * @access public
	 * @param mixed $pkvalue
	 * @return null|DataObject
	 */
	public function find($values) {
		$pks = $this->_pks;
		$values = (array) $values;
      $count = count($values);
      if(count($pks) != $count) {
         throw new Exception('Primary key values does not match with keys.');
      }
      
      $query = new sql\SelectQuery();
      $query->select('*');
      for($i = 0; $i < $count; $i++) {
         $query->where($pks[$i], $values[$i]);
      }
      
      $stmt = $this->select($query);
      return $stmt->fetch();
	}
	
	
	/**
	 * Selects data based on given $query
	 * 
	 * Additional
	 * @access public
	 * @param sql\SelectQuery $query
	 * @return Statement
	 */
	public function select(sql\SelectQuery $query) {
		$this->onSelect($query);
		
		if(!$query->connection()) { // set connetion, if not found
			$query->connection($this->getConnection());
		}
		
		if(!$query->table()) { // set the table if not found
			$query->table($this->getTableName());
		}
		
		if(empty($query->columns())) {
			$query->columns('*');
		}
		
		$query->setFetchMode(\PDO::FETCH_CLASS, $this->_rowClass);
		$stmt = $query->execute();
		
		return $stmt;
	}
	
	/**
	 * Save the object into database
	 * 
	 * If provided $obj has primary key, it will perform update operation
	 * Else, it will do an insert
	 * @access public
	 * @param DataObject $obj
	 * @return bool
	 */
	public function save(DataObject $obj) {
		
	}
	
	/**
	 * insert function.
	 * 
	 * @access public
	 * @param DataObject $obj
	 * @return void
	 */
	public function insert(DataObject $obj) {
		if(!$this->onPreInsert($obj)) return FALSE;
		
		$query = new sql\InsertQuery($this->getTableName(), $this->getConnection());
		// filter data
		$row = $this->filterSchemaData($obj->getData(), false);
		
		$insert_id = $query->execute($row);
		if($insert_id) {
			// insert id will only be provided if this model has single pk
			if(count($this->_pks) == 1) {
				$pkcol = current($this->_pks);
				$obj[$pkcol] = $insert_id;
			}
		}
		
		$this->onPostInsert($obj);
		return $insert_id;
	}
	
	/**
	 * update function.
	 * 
	 * @access public
	 * @param DataObject $obj
	 * @return void
	 */
	public function update(DataObject $obj) {
		if(!$this->onPreUpdate($obj)) return FALSE;
		if(!$obj->isModified()) return FALSE;
		
		$query = new sql\UpdateQuery($this->getTableName(), $this->getConnection());
		$this->addPrimaryKeysToQuery($query, $obj);
      
      $data = $obj->getModifiedData();
      $result = $query->execute($data);
      
      $this->onPostUpdate($obj, $result);
      
      return $result;
	}
	
	/**
	 * Delete the given $obj from database
	 * 
	 * @access public
	 * @param DataObject $obj
	 * @return void
	 */
	public function delete(DataObject $obj) {
		if(!$this->onPreDelete($obj)) return;
		
		$query = new sql\DeleteQuery($this->getTableName(), $this->getConnection());
		$this->addPrimaryKeysToQuery($query, $obj);
		$count = $query->execute();
		
		$this->onPostDelete($obj);
		return $count;
	}
	
	
	/**
	 * addPrimaryKeysToQuery function.
	 * 
	 * @access protected
	 * @param sql\Query $query
	 * @return void
	 */
	protected function addPrimaryKeysToQuery(sql\Query $query, DataObject $obj) {
		foreach($this->_pks as $pk) {
      	if(!isset($obj->$pk)) {
      		throw new \Exception('Primary key/value not found in the given class');
      	}
      	
      	$query->where($pk, $obj->$pk);
      }
	}
	
	/**
	 * filterSchemaData function.
	 * 
	 * @access protected
	 * @param array $data
	 * @return void
	 */
	protected function filterSchemaData(array $data, $refill = false) {
		$filtered = null;
		// first get all the columns available
		$columns = array_combine(array_keys($this->_schema), array_fill(0, count($this->_schema), null));
		// if both schema and data is provided,
		if($data && $this->_schema) {
			// we will only get the 
			$filtered = array_intersect_key($data, $columns);
			if($refill) {
				$filtered = $filtered + $columns;
			}
		} else {
			$filtered = $data;
		}
		
		return $filtered;
	}
	
	
	# internal events
	protected function onInit() { ; }
	protected function onSelect(sql\SelectQuery $query) { ; }
	protected function onPreInsert(DataObject $obj) { return true; }
	protected function onPostInsert(DataObject $obj) { ; }
	protected function onPreUpdate(DataObject $obj) { return true; }
	protected function onPostUpdate(DataObject $obj, $result) { ; }
	protected function onPreDelete(DataObject $obj) { return true; }
	protected function onPostDelete(DataObject $obj) { ; }
}