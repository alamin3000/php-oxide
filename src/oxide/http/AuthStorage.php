<?php
namespace oxide\http;
use oxide\base\Dictionary;
use Zend\Authentication\Storage\StorageInterface;

class AuthStorage implements StorageInterface {
	private 
      $_session,
      $_namespace;
	
	public function __construct(Dictionary $session) {
		$this->_session = $session;
		$this->_namespace = 'OXIDE_AUTH';
	}
	
	public function isEmpty() {
		return !isset($this->_session[$this->_namespace]);
	}
	
	public function read() {
		return unserialize($this->_session[$this->_namespace]);
	}
	
	public function write($content) {
		$this->_session[$this->_namespace] = serialize($content);
	}
	
	public function clear() {
		unset($this->_session[$this->_namespace]);
	}
}