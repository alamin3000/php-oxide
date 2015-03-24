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
class DataObject 
	implements \IteratorAggregate, \ArrayAccess, \Serializable, \SplSubject {
	protected
		$_data   = [],        // store all data
		$_modified = [],      // store all modified data
		$_strict	= false,     // allows only access to schema defined data
		$_observers = null;	 
	
	/**
	 * 
    * @access public
	 * @param array $data
	 */
	public function __construct(array $data = null) {
		// initially we specify that nothing is modified
		$this->_modified = [];		
		if($data) {
			$this->setData($data);
		}
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

      $this->_strict = $bool;
	}
	
	/**
    * @param array
    */
   public function setData($arr) {
	 	$this->_data = array_fill_keys(array_keys($this->_data), null);
	 	$this->_modified = [];
   	$this->addData($arr);
   }

   /**
    * appends/modifies data to current data
	 * if data already exists, then it will be modified.
	 * 
    * @param array $arr
	 * @throws Exception when data is provided is not an array
    */
   public function addData($arr) {
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
    * @param return $arr Any key/value that was not defined/set
	 */
	public function setDataForDefinedKeys($arr) {
		foreach($arr as $key => $val) {
			if(isset($this->$key)) {
				$this->$key = $val;
            unset($arr[$key]);
			}
		}
      
      return $arr;
	}
	
	
	/**
	 * Get modified key/value pairs.
	 *
	 * This will return modified keys with *modified* data, not the original
	 * @see getModifiedKeys()
	 * @return array
	 */
	public function getModifiedData() {
		$arr = [];
		foreach(array_keys($this->_modified) as $key) {
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
      return array_keys($this->_modified);
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
      if($key) return (isset($this->_modified[$key]));
      else return (count($this->_modified) > 0);
	}
	
	
	/**
	 * Restore modified values.
	 * 
	 * @access public
	 * @return void
	 */
	public function restore() {
		foreach($this->_modified as $key => $value) {
			$this->_data[$key] = $value;
		}
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
		if(!array_key_exists($key, $this->_data)) {
			if($this->_strict) throw new \Exception('Access to undefined key: ' . $key . ' in ' . get_called_class());
			else $this->_data[$key] = null;
		}
		
		// if save data is being added
		// we won't do anything
		if($this->_data[$key] === $value) return;
		
		// update data
		$this->_modified[$key] = $this->_data[$key];
		$this->_data[$key] = $value;
		$this->notify();
   }

   /**
    * get data value for given key
    *
    * performs strict check. @see strict()
    * @param string $key
    * @return mixed
    */
   public function __get($key) {
   	if(array_key_exists($key, $this->_data)) {
   		return $this->_data[$key];
   	}
		
		if($this->_strict) throw new Exception("key $key not defined. " . get_called_class());
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
		if(!array_key_exists($key, $this->_data)) {
   		if($this->_strict) throw new Exception("key $key not defined. " . get_called_class());
   		else $this->_data[$key] = null;
   	}
   	
		$this->_modified[$key] = $this->_data[$key];
		unset($this->_data[$key]);
		$this->notify();
	}
	
	## 
	
	/**
	 * attach observer for model changes
	 * 
	 * @access public
	 * @param \SplObserver $observer
	 * @return void
	 */
	public function attach(\SplObserver $observer) {
		if($this->_observers === null) {
			$this->_observers = new \SplObjectStorage();
		}
		
		$this->_observers->attach($observer);
	}
	
	
	/**
	 * Remove observer from listening to changes.
	 * 
	 * @access public
	 * @param \SplObserver $observer
	 * @return void
	 */
	public function detach(\SplObserver $observer) {
		if(!$this->_observers) return;
		$this->_observers->detach($observer);
	}

	
	/**
	 * notify objservers.
	 * 
	 * Do not call this method directly.
	 * This method will be called automatically when changes happen
	 * @access public
	 * @return void
	 */
	public function notify() {
		if($this->_observers) {
			foreach($this->_observers as $obj) {
				$obj->update($this);
			}
		}
	}
	
	## implementing 

	/**
	 * Returns the data iterator, implementing \IteratorAggregate
	 * 
	 * @return \ArrayIterator
	 */
   public function getIterator() {
   	return new \ArrayIterator($this->toArray());
   }
   
   # implementing <ArrayAccess>
   
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
	
	## implementing <Serializable> 
	
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