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
class DataObject extends \ArrayObject implements \SplSubject {
	protected
      $_schema = [],
		$_modified = [],      // store all modified data
		$_observers = null;	 
	
	/**
	 * 
    * @access public
	 * @param array $data
	 */
	public function __construct(array $data = null) {
      parent::__construct();
      $this->setFlags(self::ARRAY_AS_PROPS); // both array and property has same storage

      // initially we specify that nothing is modified
		$this->_modified = [];		
		if($data) {
			$this->setData($data);
		}
	}
	
	/**
    * Set data
    * 
    * This will replace all current values and replace with given $arr
    * @param array
    */
   public function setData($arr) {
	 	$schema = array_fill_keys(array_keys($this->getArrayCopy()), null);
      $this->exchangeArray($schema);
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
   		$this[$key] = $val;
   	}
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
			if(isset($this[$key])) {
				$this[$key] = $val;
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
		foreach($this->getModifiedKeys() as $key) {
			$arr[$key] = $this[$key];
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
         parent::offsetSet($key, $value); // use parent's setter so won't trigger
		}
	}
	
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
   
	/**
	 * offsetSet function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	function offsetSet($key, $value) {
      $curval = isset($this[$key]) ? $this[$key] : null;
      if($curval === $value) return; // same value, do nothing
		
		// update data
		$this->_modified[$key] = $curval;
      
      parent::offsetSet($key, $value);
		$this->notify();
	}

	/**
	 * offsetUnset function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	function offsetUnset($key) {
		if(!isset($this[$key])) {
         $this->_modified[$key] = null;
      } else {
         $this->_modified[$key] = $this[$key];
      }
   	
      parent::offsetUnset($key);
		$this->notify();
	}
}