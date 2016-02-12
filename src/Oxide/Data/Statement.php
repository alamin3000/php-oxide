<?php
namespace Oxide\Data;

use PDOStatement;
use PDO;

class Statement extends PDOStatement
{
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
        $this->setFetchMode(PDO::FETCH_ASSOC);
    }

    /**
     * modified to store params in local
     *
     * @access public
     * @param array $param [optional]
     * @return bool|void
     */
    public function execute($param = null)
    {
        $this->bindArray = $param;
        parent::execute($param);
    }

//   public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = 0) {
//      $obj = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
//      print '<p>fetch</p>';
//      
//      return $obj;
//   }
}