<?php
namespace oxide\security\auth;

require_once 'Zend/Auth/Storage/Interface.php';


class CookieStorage implements \Zend_Auth_Storage_Interface 
{
	private 
      $_namespace,
      $_path,
      $_ttl;
	
	public function __construct($namespace = 'oacs', $path = '/', $ttl = 43200) 
   {
		$this->_namespace = $namespace;
		$this->_path = $path;
		$this->_ttl = $ttl; // half day
	}
	
	public function isEmpty() 
   {
		if(isset($_COOKIE[$this->_namespace]) && !empty($_COOKIE[$this->_namespace])) {
			return false;
		} else {
			return true;
		}
	}
	
	public function read() 
   {
		$result = unserialize($_COOKIE[$this->_namespace]);
		return $result;
	}
	
	public function write($content) 
   {
		$content = serialize($content);
		setcookie($this->_namespace, $content, time() + $this->_ttl, $this->_path);
	}
	
	public function clear()
   {
		setcookie($this->_namespace, "", time() - 3600, $this->_path);
	}
}