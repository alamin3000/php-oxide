<?php
namespace Oxide\Data\Sql;

/**
 * Insert Query Object
 *
 * If value is strictly null or string to NULL
 * @package default
 * @author Alamin Ahmed
 * @todo need revision and otpimization
 */
class InsertQuery extends Query
{
    protected
        $_set = array(),
        $_ignore = false;


    /**
     *
     * @access public
     * @param type $bool
     */
    public function setIgnoreMode($bool)
    {
        $this->_ignore = $bool;
    }

    /**
     * sets a row of array to be inserted
     * @access public
     * @param array $row
     */
    public function set($row = null)
    {
        if ($row) {
            $this->_set = $row;
            $this->param($row);
        }
    }

    public function keysValuesString()
    {
        // prepare set
        $placeholders = [];
        $keys = array();
        foreach ($this->_set as $key => $value) {
            $placeholders[] = ":{$key}";
            $keys[] = "`{$key}`";
        }

        // build sql query
        $sql = ' (' . implode(', ', $keys) . ') ';
        $sql .= 'values (' . implode(', ', $placeholders) . ') ';

        return $sql;
    }

    /**
     *
     * @access public
     * @param type $sender
     * @return string
     */
    public function toSql($sender = null)
    {
        if ($this->_ignore) {
            $ignore = "IGNORE";
        } else {
            $ignore = "";
        }
        $sql = "INSERT {$ignore} INTO " .
            $this->table() . " " .
            $this->keysValuesString();

        return $sql;
    }

    /**
     * Perform database insert query and returns the insert id
     *
     * If param is provided, then set the values
     * this is useful to run multiple execution on single prepared statement
     * @access public
     * @param type $params
     * @return type
     * @todo verify if array is associative or vector
     */
    public function execute($params = null)
    {
        if ($params !== null) {
            $this->set($params);
        }

        parent::execute();
        return $this->_db->lastInsertId();
    }
}