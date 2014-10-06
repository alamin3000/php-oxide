<?php
namespace oxide\data;

class Statement extends \PDOStatement {
   
   /**
    *
    * @access public
    * @var type 
    */
	public 
		$dbh,
		$bindArray = null,
		$recordCount = null;
 
	/**
	 *
    * @access protected
	 * @param <type> $dbh 
	 */
	protected function __construct($dbh)
	{
		$this->dbh = $dbh;
		$this->setFetchMode(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * modified to store params in local
	 * 
    * @access public
	 * @param array $param[optional]
	 */
	public function execute($param = null) {
      $this->bindArray = $param;
      parent::execute($param);

	}
}