<?php
namespace oxide\data\model;
use oxide\data;


class EAVModel implements \IteratorAggregate {
	protected 
      $_connection = null,
      $_table = null,
      $_keyField = 'key',
      $_valueField = 'value',
      $_typeField = 'type',
      $_keyValues = [],
      $_modifiedKeys = [],
      $_filters = [],
      $_loaded = false;
	
	const 
      ACTION_INSERT = 1,
      ACTION_UPDATE = 2,
      ACTION_DELETE = 3;
	
	/**
    *
    * @access publica
    * @param Connection $connection
    * @param string $table
    * @param array $filters
    */
	public function __construct(data\Connection $connection = null, $table = null, $filters = array()) {	
		if($connection) $this->connection($connection);
		$this->_table = $table;
		$this->_filters = $filters;
	}
   
   /**
    * Configure the model
    * 
    * @param array $param
    */
   public function configure(array $param) {
      if(isset($param['key'])) $this->_keyField = $param['key'];
      if(isset($param['value'])) $this->_valueField = $param['value'];
   }

   
   /**
    *
    * @access public
    * @param array $config 
    */
   public static function createWithParam(array $config) {
      
   }

   /**
    * set/get current shared database connection
    *
    * this method will be used by the class to connect to database.
    * If no connection is found then it will attempt to retrive shared connection from the data\Connection object
    * @param oxide\data\Connection $conn
    * @return oxide\data\Connection
    */
   public function connection(data\Connection $conn = null) {
      if($conn != null) {
         $this->_connection = $conn;
      }

      if($this->_connection === null) {
         $this->_connection = data\Connection::sharedInstance();
      }

      return $this->_connection;
   }

   /**
    * table property access method
    * 
    * @param string $table
    */
   public function table($table = null) {
      if($table) {
         $this->_table = $table;
      }

      return $this->_table;
   }

   /**
    * set retrival filters
    *
    * this is important
    * @param array $filters
    */
	public function setFilter($filters) {
		$this->_filters = $filters;
	}
   
   /**
    * filters the model
    * 
    * @param type $filters
    */
   public function filter($filters) {
      $this->_filters = $filters;
   }

   /**
    *
    * @return type 
    */
   public function hasFilter() {
      return count($this->_filters) > 0;
   }

	/**
	 * Checks if attributes has been loaded from the database or not
	 * 
	 * @return bool
	 */
	public function isLoaded() {
		return $this->_loaded;
	}
	
	/**
    * loads the attributes into memory
	 * 
    * @param bool $reload indicate if should forcely reload data from server
	 * @throws Exception
    */
	public function load($reload = false) {
      if($this->_loaded && $reload == false) return;
      
		$query = new data\sql\SelectQuery($this->table(), $this->connection());
		$query->select('*');
		if($this->_filters) {
         foreach($this->_filters as $key => $value) {
            $query->where($key, $value);
         }
		} else {
         throw new \Exception('filters are not defined');
      }

		$result = $query->execute();
		foreach($result as $attrib) {	
			$this->_keyValues[$attrib[$this->_keyField]] = $attrib[$this->_valueField];
		}

      // flag that attributes are loaded
      $this->_loaded = true;
	}


	/**
	 * set data to the object
	 * 
	 * @param array $data
	 */
	public function setData($data) {
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	
	/**
	 * this is alias to magic __set method
	 * this may sometimes be required if key is non-standard to php variable name
	 * @access public
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
	   $this->$key = $value;
	}
   
   public function get($key) {
      return $this->$key;
   }
   
   public function remove($key) {
      unset($this->$key);
   }
   
   public function has($key) {
      return isset($this->$key);
   }

	/**
	 * deletes ALL attributes available for current filter
	 *
	 * @throws Exception
	 * @param Connection $db
	 * @return int
	 */
	public function deleteAll(data\Connection $db = null) {
		if($db) $this->_db = $db;
		if(!$this->_filters) throw new Exception('filters are not set ' . __METHOD__);
		
		$query = new data\sql\DeleteQuery($this->_db, $this->_table);
		foreach($this->_filters as $key => $value) {
			$query->where($key, $value);
		}
		
		return $query->execute();
	}

   /**
    * saves to database
    *
    * This will perform all data manupulation queries when applicable,
    * including INSERT, UPDATE, DELETE operations
    *
    * Filters must be set before saving.
    *
	 * @throws Exception
    * @return bool
    */
	public function save() {
		if(empty($this->_filters)) {
			throw new \Exception('Filters are not set ' . __METHOD__);
		}
		
		// check for all modified keys
		foreach($this->_modifiedKeys as $key => $action) {
			switch($action) {
				// insert new attribute
				case self::ACTION_INSERT:
					$this->_insert($key);
					break;

				// update current attribute
				case self::ACTION_UPDATE:
					$this->_update($key);
					break;

				// delete an attribute
				case self::ACTION_DELETE:
					$this->_delete($key);
					break;
					
				default;
			}
         
         unset($this->_modifiedKeys[$key]);
		}
	}

	/**
	 * inserts new attribute
	 * @param string $key
	 */
	protected function _insert($key) {
		$query = new data\sql\InsertQuery($this->table(), $this->connection());
		$row = $this->_filters;
		$row[$this->_keyField] = $key;
		$row[$this->_valueField] = $this->_keyValues[$key];
		$query->execute($row);
	}
	
   /**
    *
    * @param type $key 
    */
	protected function _update($key) {
		$query = new data\sql\UpdateQuery($this->table(), $this->connection());
		foreach($this->_filters as $k => $v) {
			$query->where($k, $v);
		}
		$query->where($this->_keyField);
		$query->set($this->_valueField);
		
		$row = [$this->_valueField => $this->_keyValues[$key], $this->_keyField => $key];
		$query->execute($row);
	}
	
   /**
    *
    * @param type $key 
    */
	protected function _delete($key) {
		$query = new data\sql\DeleteQuery($this->table(), $this->connection());
		foreach($this->_filters as $k => $v) {
			$query->where($k, $v);
		}
		$query->where($this->_keyField);
		
		$row = [$this->_keyField => $key];
		$query->execute($row);
		unset($this->_keyValues[$key]);
	}
	
   /**
    *
    * @access public
    * @param type $key
    * @return type 
    */
	public function __get($key) {
		if(array_key_exists($key, $this->_keyValues)) {
			return $this->_keyValues[$key];
		} else {
			return null;
		}
	}
	
   /**
    *
    * @access public
    * @param type $key
    * @param type $value
    * @return type 
    */
	public function __set($key, $value) {
		if(array_key_exists($key, $this->_keyValues)) {
			// do nothing if value is same
			if($this->_keyValues[$key] == $value) return;

			// check whether or not value already has been modified
			if(array_key_exists($key, $this->_modifiedKeys)) {
				// value already has been modified and not saved
				// just update value without modifying last action value
				$this->_keyValues[$key] = $value;
			} else {
				// newly modifed, flag for update
				$this->_keyValues[$key] = $value;
				$this->_modifiedKeys[$key] = 	self::ACTION_UPDATE;
			}
		} else {
			// key does not exists,
			// this will be new attribute
			$this->_modifiedKeys[$key] = self::ACTION_INSERT; // flag for insert
			$this->_keyValues[$key] = $value; // add the value for save
		}
	}
	
   /**
    *
    * @access public
    * @param type $key
    * @return type 
    */
	public function __isset($key) {
		return array_key_exists($key, $this->_keyValues);
	}
	
   /**
    *
    * @access public
    * @param type $key
    * @return type 
    */
	public function __unset($key) {
		// if key does not exists, simply don't do anything
		if(!array_key_exists($key, $this->_keyValues)) return false;
		if(!array_key_exists($key, $this->_modifiedKeys)) {
			// key is found, but not modified yet,
			$this->_modifiedKeys[$key] = self::ACTION_DELETE;
		} else {
			// key already modified
			// we need to know if it is for insert, in that case we just remove it from the array
			if($this->_modifiedKeys[$key] == self::ACTION_INSERT) {
				unset($this->_keyValues[$key]);
				unset($this->_modifiedKeys[$key]);
				// should not have any trace of this key
			} else {
				$this->_modifiedKeys[$key] = self::ACTION_DELETE;
			}
		}
	}

	/**
	 * returns associative array of current data
	 * 
	 * @return array
	 */
	public function toArray() {
		return $this->_keyValues;
	}

	/**
	 * get the iterator.  implementing iterator aggregator
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return  new \ArrayIterator($this->_keyValues);
	}
}