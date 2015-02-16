<?php
namespace oxide\data\model;
use Exception;

/**
 * Data object class
 *
 * simple array based data object model
 * provides varies ways to store/access data in a single class
 * provides iteration and arary access
 */
class DataObject implements \IteratorAggregate, \ArrayAccess, \Countable, \Serializable {
	
	protected static
		$_schema = [];
	
	protected
		$_data   = [],        // store all data
		$_modified = [],      // store all modified data
		$_strict	= false;     // allows only access to defined data
	
	/**
	 * 
    * @access public
	 * @param array $data
	 */
	public function __construct(array $data = null) {
		if($data) {
			$this->setData($data);
		}
	}


   /**
    * get class name
    *
    * @return string
    */
   public static function getClassName() {
      return get_called_class();
   }

   /**
    * get/set strict mode
    *
    * If strict mode is set then access to undefined property will throw exception.
	 * Can not set to strict mode before adding any data
	 * 
    * Restrict access to the data only within what already defined.  Meaning access to data that is not already stored or defined 
    * will throw exception.
    * @access public
    * @param type $bool
    * @return type
    * @throws \Exception
    * @throws Exception 
    */
	public function strict($bool = null) {
      if($bool === null) {
         return $this->_strict;
      }

      if($bool == true) {
         if(empty(static::$_schema))
            throw new Exception('Can not enable strict mode when schema is not defined for ' . static::getClassName());
      }
      
      $this->_strict = $bool;
	}
	
	/**
    * @param array
    */
   public function setData(array $arr) {
	   if(static::$_schema) {
		   $this->_data = static::$_schema;
	   } else {
		   $this->_data = [];
	   }
   	$this->addData($arr);
   }

   /**
    * appends/modifies data to current data
	 * if data already exists, then it will be modified.
	 * 
    * @param array $arr
	 * @throws Exception when data is provided is not an array
    */
   public function addData(array $arr) {
   	foreach($arr as $key => $val) {
   		$this->$key = $val;
   	}
   }

	/**
	 * get the current data array
	 *
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Sets data for the currently defined keys only.
	 * 
	 * Any other data provided in the $arr will be ignored
	 * @param array $arr
	 */
	public function setDataForDefinedKeys($arr) {
		foreach($arr as $key => $val) {
			if(isset($this->$key)) {
				$this->$key = $val;
			}
		}
	}
	
	/**
	 * Get modified key/value pairs.
	 *
	 * To get only modified keys, see getModifiedKeys()
	 * @see getModifiedKeys()
	 * @return array
	 */
	public function getModifiedData() {
		$arr = [];
		foreach($this->_modified as $key) {
			$arr[$key] = $this->$key;
		}
		
		return $arr;
	}
	
	/**
	 * Get modified key array.
	 *
	 * @see getModifiedData()
	 * @return array
	 */
	public function getModifiedKeys() {
      return $this->_modified;
	}
	
	/**
	 * Check if any value has been modifed.
	 *
	 * If $key is given, then only checks if given $key is modified,
	 * else it will return true if ANY data is modified.
	 * 
	 * @return bool
	 * @param string $key
	 */
	public function isModified($key = null) {
      if($key)
         return in_array($key, $this->_modified);
      else
         return (count($this->_modified) > 0);
	}
	
   /**
    * sets data to the object
    *
    * performs strict check when enabled. @see strict()
    * 
    * @return 
    * @param string $key
    * @param mixed $value
    * @return void
    */
   public function __set($key, $value) {	
		if(!array_key_exists($key, static::$_schema)) {
			if($this->_strict) throw new Exception("key: $key NOT found while writing in " . static::getClassName());
			$this->_data[$key] = null;
		}
		
		if($this->_data[$key] === $value) return;
		
		// update data
		$this->_data[$key] = $value;
		$this->_modified[] = $key;
   }

   /**
    * get data value for given key
    *
    * performs strict check. @see strict()
    * @param string $key
    * @return mixed
    */
   public function __get($key) {
   	if(array_key_exists($key, static::$_schema)) {
   		return $this->_data[$key];
   	}
		
		if($this->_strict) throw new Exception("key $key not  found while reading from " . static::getClassName());
		return null;
   }


   /**
    * isset override
    *
    * @param string $key
    * @return bool
    */
   public function __isset($key) {
   	return isset($this->_data[$key]);
   }

	/**
	 *
	 * @param string $key
	 */
	public function __unset($key) {
		$this->_data[$key] = null;
	}

   
   /**
    * Implements Countable interface
    * 
    * Returns the number of data available
    * @access public
    * @return int 
    */
	public function count() {
		return count($this->_data);
	}

	/**
	 * Returns the data iterator, implementing \IteratorAggregate
	 * 
	 * @return \ArrayIterator
	 */
   public function getIterator() {
   	return new \ArrayIterator($this->toArray());
   }
   
   
	/**
	 * offsetSet function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	function offsetSet($key, $value) {
		return $this->__set($key,$value);
	}
	
	
	/**
	 * offsetGet function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	function offsetGet($key) {
		return $this->__get($key);
	}
	
	
	/**
	 * offsetUnset function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	function offsetUnset($key) {
		$this->__unset($key);
	}
	
	
	/**
	 * offsetExists function.
	 * 
	 * @access public
	 * @param mixed $offset
	 * @return void
	 */
	function offsetExists($offset) {
		return $this->__isset($offset);
	}
	
	/**
	 * serialize function.
	 * 
	 * @access public
	 * @return void
	 */
	public function serialize() {
		return serialize($this->_data);
	}
	
	
	/**
	 * unserialize function.
	 * 
	 * @access public
	 * @param mixed $str
	 * @return void
	 */
	public function unserialize($str) {
		$this->_data = unserialize($str);
	}
}