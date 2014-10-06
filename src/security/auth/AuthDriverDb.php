<?php
require_once 'oxide/database/Query/Select.php';
require_once 'oxide/auth/Adapter/Interface.php';

/**
 * Authentication Adapter class.
 *
 * performs authentication using Database.
 * @author Alamin Ahmed <aahmed753@gmail.com>
 * @package Oxide
 */
class AuthDriverDb implements Oxide_Auth_Adapter_Interface {
	protected $_Db = NULL;
	protected $_Query = null;
	protected $_table = '';
	protected $_identityColumn = '';
	protected $_passwordColumn = '';
	protected $_identity = '';
	protected $_password = '';
	protected $_authenticated = false;
	
	/**
	 * construction
	 *
	 * requires all database related information.
	 * @access public
	 * @param $dbConn Oxide_Db
	 * @param $tableName string
	 * @param $loginField string
	 * @param $passField string
	 */
	public function __construct($Db, $table, $identityColumn = 'username', $passwordColumn = 'password') {
		/*
		 * store/initialize local vars/objects
		 */
		$this->_Db = $Db;
		$this->_table = $table;
		$this->_identityColumn = $identityColumn;
		$this->_passwordColumn = $passwordColumn;
		
		/*
		 * create base Query object and store for later reference
		 */
		$this->_Query = new Oxide_Db_Query_Select($Db, $table);
	}
	
	/**
	 * sets credential values
	 * @access public
	 * @param $login string
	 * @param $password string
	 */
   public function setCredential($identity, $password) {
      $this->_identity = $identity;
      $this->_password = $password;
   }

	public function getQuery() {
		return $this->_Query;
	}
	
	/**
	 */
	public function getIdentity() {
		return $this->_identity;
	}
	
	/**
	 * authenticate
	 * 
	 * performs authentication against database.
	 * this authentication performs both login and password at once.  therefore there is no way of telling
	 * which one was incorrect when login fails.
	 * @access public
	 * @param $login string
	 * @param $login string
	 * @throws Exception
	 * @todo Need to validate login and password.
	 * @return boolean
	 */
	public function authenticate() {
		
      // sets authentication incomplete
      $this->_authenticated = false;

		// performs validations
		$this->_validateCredentials();
      
      $Query = $this->_Query;
      $Query->select('*');
      $Query->where($this->_identityColumn, $this->_identity);
      $Query->where($this->_passwordColumn, $this->_password);
      $Smnt = $Query->execute();      
		
		// return all records found
		$row = $Smnt->fetchAll();
		
		// checks authentication
		if(count($row) < 1) {
			// no record found.
			// login failed.
			return false;
		} elseif(count($row) > 1)  {
			// more then one record found.
			// ambigious.  this should be an error.
         return false;
		}
		
		return true;
	}
	
	/**
	 * checks if this adapter has been authenticated.
	 * @access public
	 * @return boolean
	 */
	public function isAuthenticated() {
      return $this->_authenticated;
	}
	
	/**
	 * performs credential validation
	 * @access private
	 */
   private function _validateCredentials() {
   
   }
}
?>