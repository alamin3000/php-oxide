<?php
namespace oxide\data\model;
use oxide\data;
use oxide\data\sql;

/**
 * Simple to active record model, 
 * 
 * model provides a single object to both retrive and manupulate
 * object from single database table.
 *
 * some rules and assumption of the database table design
 * * all fields/columns are lower cased, OR database server is case insancitive for fields/column names
 * * table name is capitilized OR database is case insensative for table name, OR subclass defines table name
 * * default primary key field name is 'pk', however, this can be changed by subclassing
 * * default table name is the name of the sub class name, unless changed by subclass.
 */
abstract class ActiveRecord extends DataObject {
   protected static
      /**
       * @var data\Connection Shared connection among all records
       */
      $_sharedConnection = null;
   
   protected
      /**
       * @var data\Connection local connection for the object
       */
      $_connection = null;

   protected static
      $_pk = 'pk',
      $_table = null;

   /**
    * construct the Cingle object
    * 
    * @param array $data
    * @param data\Connection $conn
    */
   public function __construct(array $data = null, data\Connection $conn = null) {
      parent::__construct($data);
      if($conn) {
         $this->connection($conn);
      }
      
      $this->onInit();
   }
   
   /**
    * 
    * @param string $tablename
    * @param string $pkfield
    */
   static protected function setTableInfo($tablename, $pkfield) {
      static::$_table = $tablename;
      static::$_pk = $pkfield;
   }
   
   /**
    * Get active connection for the record
    * 
    * If local connection not found, it will attempt to return the shared connection
    * @return data\Connection
    */
   public function connection(data\Connection $conn = null) {
      if($conn) {
         $this->_connection = $conn;
      } else {
         if($this->_connection) 
            return $this->_connection;
         else
            return static::sharedConnection();
      } 
   }
   
   /**
    * Get the shared connection
    * 
    * @return data\Connection
    */
   public static function sharedConnection(data\Connection $conn = null) {
      if($conn) {
         static::$_sharedConnection = $conn;
      } else {
	      if(static::$_sharedConnection) {
		      return static::$_sharedConnection;
	      } else {
		      return data\Connection::sharedInstance();
	      }
      }
   }

   /**
    * returns database table name represented by this model
    *
    * if table name is not defined by the sub class then
    * the name of the Class will be used as database table
    * @return string
    */
   public static function getTable() {
      if(static::$_table) {
         return static::$_table;
      } else {
         /*
          * classname might have namespaces
          * we need to remove them
          */
         $clsname = get_called_class();
         $clsparts= explode('\\', $clsname);
         return end($clsparts);
      }
   }
   
   
   /**
    * getPkColumn function.
    * 
    * @access public
    * @static
    * @return void
    */
   public static function getPkColumn() {
      return static::$_pk;
   }

   
   /**
    *
    * @access public
    * @return int 
    */
   public function getPkValue() {
      return $this->{static::getPkColumn()};
   }

   /**
    * saves the current object
    *
    * if primary key is not defined, then insert operation will performed
    * else current changes will be updated
    *
    * only modified keys will be changed in the database
    * @return bool
    */
   public function save() {      
      $this->onSave();
      if(!$this->isModified()) {
	      return false;
	   }
      $pkfield = static::getPkColumn();
      $pkvalue = (isset($this->$pkfield)) ? $this->$pkfield : 0;
      
      if(!$this->onPreSave()) return false;
      if($pkvalue) { // update
         $result = $this->_update();
      } else { // insert
         $result = $this->_insert();
      }
      
		$this->onPostSave();
		return $result;
   }

   /**
    * removes the current data row from the database
    * @return int
    */
   public function delete() {
      // event call and check
      $pre = $this->onPreDelete();
      if($pre === null) {
         throw new \oxide\util\Exception("onPreDelete must return boolean value");
      } else {
         if(!$pre) {
            return FALSE;
         }
      }
      
      $conn = $this->connection();
      $pkfield = static::getPkColumn();
      $pkvalue = $this[$pkfield];
      if(!$pkvalue) return false;

      $query = new sql\DeleteQuery(static::getTable(), $conn);
		$query->where($pkfield, $pkvalue);
		$result = $query->execute();

      $this->onPostDelete($result);
		return $result;
   }

   protected function _insert() {
      // event call and check
      $pre = $this->onPreInsert();
      if($pre === null) {
         throw new \oxide\util\Exception("onPreInsert must return boolean value");
      } else {
         if(!$pre) {
            return \FALSE;
         }
      }

      $db = $this->connection();
      $table = static::getTable();
      $pkfield = static::getPkColumn();
		$data = $this->getModifiedData();

      $query = new sql\InsertQuery($table, $db);
		$id = $query->execute($data);
		$this->$pkfield =  $id;

      // event call
      $this->onPostInsert($id);
      return $id;
   }

   protected function _update() {
      // event call and check
      $pre = $this->onPreUpdate();
      if($pre === null) {
         throw new \oxide\util\Exception("onPreUpdate must return boolean value");
      } else {
         if(!$pre) {
            return \FALSE;
         }
      }

      $db = $this->connection();
      $table = static::getTable();
      $pkfield = static::getPkColumn();
      $pkvalue = $this[$pkfield];
		$data = $this->getModifiedData();

      // update data in the database
		$query = new sql\UpdateQuery($table, $db);
		$query->where($pkfield, $pkvalue);
		$query->set($data);
		$result = $query->execute();

      $this->onPostUpdate($result);
		return $result;
	}
   
   /**
    * returns objects based from executing given query
    *
    * This method is called for ALL select/find methods.
    * this object will automatically set the table name and database connection
    *
    * @param oxide\data\sql\SelectQuery $query
    * @return Statement
    */
   public static function select(sql\SelectQuery &$query = null) {
      $table = static::getTable();
      if($query == null) {
         $query = new sql\SelectQuery();
         $query->select('*');
      }
      if(!$query->connection()) {
         $query->connection(static::sharedConnection());
      }
      $query->table($table);
      $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class());
      
      static::onPreSelect($query);
      $stmt = $query->execute();
      static::onPostSelect($stmt);

      return $stmt;
   }

   /**
    * returns a data row using primary key value
    * 
    * @param int $pkval
    * @return Cingle
    */
   public static function find($pkval) {
      $query = new sql\SelectQuery();
      $pk = static::getPkColumn();
      $query->select('*');
      $query->where($pk, $pkval);

      $stmt = static::select($query);
      return $stmt->fetch();
   }
   
   /**
    * 
    * @return array
    */
   public static function findAll() {
      $stmt = static::select();
      return $stmt->fetchAll();      
   }
	
   /**
    *
    * @access public
    * @param type $method
    * @param type $arg
    * @return type
    * @throws \Exception
    * @throws \oxide\util\Exception 
    */
   public static function __callStatic($method, $arg) {
      $fetch = false;
      if(substr($method, 0, 6) == 'findBy') {
         $str_field = (substr($method, 6));
         $fetch = true;
      } else if(substr($method, 0, 8) == 'selectBy') {
         $str_field = (substr($method, 8));
      } else {
         throw new \Exception("$method is not defined.");
      }

      $fields = explode('And', $str_field);      
      if($fields) {
         $query = new sql\SelectQuery();
         $query->select('*');

         $i = 0;
         foreach($fields as $field) {
            if(!array_key_exists($i, $arg)) {
               throw new \oxide\util\Exception("$method is missing value");
            }

				if(is_null($arg[$i])) {
					$query->where($field, NULL, data\Connection::OP_NULL);
				} else {
					$query->where($field, $arg[$i]);
				}
				
            $i++;
         }
      } else {
         throw new \Exception("$method fields not specified.");
      }

      $stmt = self::select($query);
      if($fetch) {
         return $stmt->fetch();
      } else {
         return $stmt;
      }
   }

   // hock functitons
	protected static function onPreSelect(sql\SelectQuery $select) {}
	protected static function onPostSelect(data\Statement $stmt) {}
   protected function onInit() { }
	protected function onPreInsert() {return true;}
	protected function onPostInsert($id) {}
	protected function onPreUpdate() {return true;}
	protected function onPostUpdate($result) {}
	protected function onPreDelete() {return true;}
	protected function onPostDelete($result) {}
	protected function onSave() {}
   protected function onPreSave() { return true; }
   protected function onPostSave() { }
}