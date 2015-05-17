<?php
namespace oxide\app\auth;
use oxide\http\Session;
use Zend\Authentication\Storage\StorageInterface;

class SessionStorage implements StorageInterface {
	private 
      $_session,
      $_namespace;
	
	public function __construct(Session $session) {
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