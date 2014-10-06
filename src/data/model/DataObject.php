<?php
namespace oxide\data\model;
use oxide\util\Exception;

/**
 * Data object class
 *
 * simple array based data object model
 * provides varies ways to store/access data in a single class
 * provides iteration and arary access
 */
class DataObject implements \IteratorAggregate, \ArrayAccess, \Countable
{
	protected
		$_data   = array(),        // store all data
		$_tmp		= array(),        // store temporary data
		$_modified = array(),      // store all modified data
		$_strict	= false;          // 
	
	/**
	 *
    * @access public
	 * @param array $data
	 */
	public function __construct($data = null)
	{
		if($data) {
			$this->setData($data);
		}
		
		// initially we specify that nothing is modified
		$this->_modified = array();
	}


   /**
    * get class name
    *
    * @return string
    */
   
   /**
    *
    * @access public
    * @return type 
    */
   public static function getClassName()
   {
      return get_called_class();
   }

   /**
    * get/set strict mode
    *
    * If strict mode is set then access to undefined property will throw exception.
	 * Can not set to string mode before adding any data
	 * 
    * @param bool $bool
    * @return bool
	 * @throws Exception
    */
   
   /**
    * Restrict access to the data only within what already defined.  Meaning access to data that is not already stored or defined 
    * will throw exception.
    * 
    * @access public
    * @param type $bool
    * @return type
    * @throws \Exception
    * @throws Exception 
    */
	public function strict($bool = null)
	{
      if($bool === null) {
         return $this->_strict;
      }

      if(!is_bool($bool)) {
         throw new \Exception("argument needs to be boolean");
      }

      if($bool == true) {
         if(count($this->_data) == 0)
            throw new Exception('Can not enable strict mode when there is no data avaialble for ' . static::getClassName());
      }
      
      $this->_strict = $bool;
	}
	
	/**
    * set object data from an associative array
    * replaces all current data with given data
    *
    * to add individual values, @see addData()
    * @access public
	 * @throws Exception when data is not an array.
    * @param array
    */
   
   /**
    *
    * @access public
    * @param type $arr
    * @throws Exception 
    */
   public function setData($arr) 
   {
   	if(!is_array($arr)) {
   		throw new Exception('data must be an array ' . __METHOD__);
   	}
   	
   	$this->_data = $arr;
   }

   /**
    * appends/modifies data to current data
	 * if data already exists, then it will be modified.
	 * 
    * @param array $arr
	 * @throws Exception when data is provided is not an array
    */
   
   /**
    *
    * @access public
    * @param type $arr
    * @throws Exception 
    */
   public function addData($arr)
   {
   	if(!is_array($arr)) {
   		throw new Exception('data must be an array ' . __METHOD__);
   	}

   	foreach($arr as $key => $val) {
   		$this->$key = $val;
   	}
   }

	/**
	 * get the current data array
	 *
	 * same as toArray()
	 *
	 * @see toArray()
	 * @return array
	 */
   
   /**
    *
    * @access public
    * @return type 
    */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Sets data for the currently defined keys only.
	 * 
	 * Any other data provided in the $arr will be ignored
	 * @param array $arr
	 */
   
   /**
    *
    * @access public
    * @param type $arr 
    */
	public function setDataForDefinedKeys($arr)
	{
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
   
   /**
    *
    * @access public
    * @return type 
    */
	public function getModifiedData()
	{
		$arr = array();
		foreach($this->_modified as $key) {
			$arr[$key] = $this->$key;
		}
		
		return $arr;
	}
	
	/**
	 * Get modified key array.
	 *
	 * @see getModifiedData(0
	 * @return array
	 */
   
   /**
    *
    * @access public
    * @return type 
    */
	public function getModifiedKeys()
	{
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
   
   /**
    *
    * @access public
    * @param type $key
    * @return type 
    */
	public function isModified($key = null)
	{
      if($key)
         return in_array($key, $this->_modified);
      else
         return (count($this->_modified) > 0);
	}
      
	/**
	 * Return object as an array.
	 *
	 * @see getData()
	 * @return 
	 */
   
   /**
    *
    * @access public
    * @return type 
    */
   public function toArray() 
   {
   	return $this->_data;
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
   public function __set($key, $value)
   {	
		if(!array_key_exists($key, $this->_data)) {
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
   
   /**
    *
    * @access public
    * @param type $key
    * @return null
    * @throws Exception 
    */
   public function __get($key) 
   {
   	if(array_key_exists($key, $this->_data)) {
   		return $this->_data[$key];
   	}
		
		if($this->_strict) throw new Exception("key $key not  found while reading from " . static::getClassName());
		return null;
   }


   /**
    * unset override
    *
    * @param string $key
    * @return bool
    */
   
   /**
    *
    * @access public
    * @param type $key
    * @return type 
    */
   public function __isset($key)
   {
   	return array_key_exists($key, $this->_data);
   }

	/**
	 *
	 * @param string $key
	 */
   
   /**
    *
    * @access public
    * @param type $key 
    */
	public function __unset($key)
	{
		$this->_data[$key] = null;
	}

   
   /**
    *
    * @access public
    * @return type 
    */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Returns the data iterator, implementing \IteratorAggregate
	 * 
	 * @return \ArrayIterator
	 */
   
   /**
    *
    * @access public
    * @return \ArrayIterator 
    */
   public function getIterator()
   {
   	return new \ArrayIterator($this->toArray());
   }
   
   /** 
	 * Defined by ArrayAccess interface
	 *
	 * Set a value given it's key e.g. $A['title'] = 'foo'; 
	 * @param mixed key (string or integer) 
	 * @param mixed value 
	 * @return void 
	 */ 
	function offsetSet($key, $value)
	{
		return $this->__set($key,$value);
	}
	
	/** 
	 * Defined by ArrayAccess interface 
	 * Return a value given it's key e.g. echo $A['title']; 
	 * @param mixed key (string or integer) 
	 * @return mixed value 
	 */
	function offsetGet($key)
	{
		return $this->__get($key);
	}
	
	/** 
	 * Defined by ArrayAccess interface 
	 * Unset a value by it's key e.g. unset($A['title']); 
	 * @param mixed key (string or integer) 
	 * @return void 
	 */ 
	function offsetUnset($key)
	{
		$this->__unset($key);
	}
	
	/**
	* Defined by ArrayAccess interface
	*
	* Check value exists, given it's key e.g. isset($A['title'])
	* @param mixed key (string or integer)
	* @return boolean
	*/
	function offsetExists($offset)
	{
		return $this->__isset($offset);
	}
	 
   /**
    *
    * @access public
    * @return type 
    */
	 
	public function __toString()
	{
		return \oxide\util\Debug::dump($this->_data, false);
	}
}