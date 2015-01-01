<?php
namespace oxide\app\auth;
use oxide\http\Session;
use Zend\Authentication\Storage\StorageInterface;

class SessionStorage implements StorageInterface 
{
	private 
      $_session,
      $_namespace;
	
	public function __construct() 
   {
		$this->_session = Session::getInstance();
		$this->_namespace = 'OXIDE_AUTH';
	}
	
	public function isEmpty() 
   {
		if($this->_session->read($this->_namespace, false)) {
			return false;
		} else {
			return true;
		}
	}
	
	public function read() 
   {
		$result = unserialize($this->_session->read($this->_namespace));
		return $result;
	}
	
	public function write($content) 
   {
		$this->_session->write($this->_namespace, serialize($content));
	}
	
	public function clear() 
   {
		$this->_session->delete($this->_namespace);
	}
}