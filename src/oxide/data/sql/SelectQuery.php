<?php
namespace Oxide\Data\Sql;

class SelectQuery extends Query
{
    protected
        $_distinct = false,
        $_columns = [],
        $_limit = [],
        $_order = [],
        $_groups = [];

    /**
     * Resets all internal data for new query
     *
     * @access public
     */
    public function reset()
    {
        parent::reset();
        $this->_columns = [];
        $this->_join = [];
        $this->_limit = [];
        $this->_order = [];
        $this->_groups = [];
    }

    /**
     * add column(s) to the select list.
     *
     * this is alais for columns() method
     * @access public
     * @param mixed $columns
     * @param string $schema
     */
    public function select($columns, $table = null)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $this->columns($columns, $table);

    }

    /**
     * select distinct record
     *
     * @access public
     * @param bool $bool
     */
    public function distinct($bool = true)
    {
        $this->_distinct = $bool;
    }


    /**
     * same as table() function
     *
     * does not allow multiple tables in the from clause
     * additional tables must be added by join
     * @access public
     * @param string $table
     * @param string $schema
     */
    public function from($table, $as = null)
    {
        if ($as) {
            if (is_array($table)) {
                throw new \Exception('If table alias is provided, table param cannot be an array');
            }
            $table = "{$table} as $as";
        } else {

        }
        $this->table($table);
    }

    /**
     * add a single column to the column list
     *
     * this method provide a way to include any expression as column
     * @access public
     * @param string $name
     * @param string $expr
     */
    public function column($name, $expr = null)
    {
        if ($expr) {
            $this->_columns[$name] = "{$expr} as {$name}";
        } else {
            $this->_columns[$name] = $name;
        }
    }

    /**
     * adds multiple columns
     *
     * @access public
     * @see column
     * @param array $cols
     * @param string $table
     */
    public function columns($cols = null, $table = null)
    {
        if ($cols === null) {
            return $this->_columns;
        }

        if (!is_array($cols)) {
            $cols = (array)$cols;
        }

        if ($table) {
            $table = "{$table}.";
        }

        foreach ($cols as $col) {
            $this->_columns[$col] = $table . $col;
        }
    }

    /**
     * Add sort order to the query
     *
     * @access public
     * @param string $field
     * @param string $order
     */
    public function order($field, $order = '')
    {
        $this->_order[$field] = $order;
    }


    /**
     * Set columns to be used for GROUP clause
     *
     * @access public
     * @param type $fields
     */
    public function group($fields)
    {
        $this->_groups = (array)$fields;
    }


    /**
     * Set the LIMIT clause
     *
     * @access public
     * @param int $offset
     * @param int $count
     */
    public function limit($offset, $count)
    {
        $this->_limit['offset'] = (int)$offset;
        $this->_limit['count'] = (int)$count;
    }


    /**
     * Set page size using limit clause
     *
     * & @see limit
     * @access public
     * @param int $num
     * @param int $size
     */
    public function page($num, $size)
    {
        $offset = max(0, ($num - 1) * $size);
        $this->limit($offset, $size);
    }

    /**
     * retrive number of records for the current query
     *
     * @access public
     * @return int
     */
    public function retriveRecordCount($cache = true)
    {
        $sql = $this->renderWithoutLimit();
        $smnt = $this->_db->prepare($sql);
        $smnt->execute($this->_param);
        $count = $smnt->fetchColumn();
        return $count;
    }

    /**
     * retrive number of pages based on given $pagesize
     *
     * @access public
     * @param int $pagesize
     * @return int
     */
    public function retrivePageCount($pagesize, $cache = true)
    {
        return ceil($this->retriveRecordCount($cache) / max($pagesize, 1));
    }


    /**
     * Renders a complete SQL statement, but omitting the LIMIT clause
     *
     * This is specially useful for getting record count.
     * @access protected
     * @return type
     */
    protected function toWithoutLimitSql()
    {
        $sql = "SELECT count(*) as count ";
        $sql .= $this->toFromSql();
        $sql .= $this->toJoinSql();
        $sql .= $this->toWhereSql();
        $sql .= $this->toGroupSql();
        $sql .= $this->toOrderSql();

        return $sql;
    }


    /**
     * Renders FROM portion of the SQL statement
     *
     * @access protected
     * @return type
     */
    protected function toFromSql()
    {
        return 'FROM ' . implode(', ', (array)$this->_table) . ' ';
    }


    /**
     * Renders the SELECT portion of the SQL statement
     *
     * @access protected
     * @return type
     */
    public function toSelectSql()
    {
        if ($this->_distinct) {
            $distinct = "DISTINCT ";
        } else {
            $distinct = "";
        }
        return 'SELECT ' . $distinct . implode(', ', array_values($this->_columns)) . ' ';
    }


    /**
     * Renders Group portion of the SQL statement
     *
     * @access protected
     * @return string
     */
    public function toGroupSql()
    {
        if ($this->_groups) {
            return 'GROUP BY ' . implode(', ', $this->_groups) . ' ';
        } else {
            return '';
        }
    }


    /**
     * Renders ORDER portion of the SQL statement
     *
     * @access protected
     * @return string
     */
    public function toOrderSql()
    {
        $sql = '';
        if (count($this->_order) > 0) {
            $_order = '';
            foreach ($this->_order as $field => $order) {
                if (!empty($order)) {
                    $_order .= "{$field} {$order},";
                } else {
                    $_order .= "{$field},";
                }
            }

            $_order = rtrim($_order, ',');
            $sql = ' ORDER BY ' . $_order . ' ';
        }


        return $sql;
    }


    /**
     * Renders LIMIT portion of the SQL statement
     *
     * @access protected
     * @return string
     */
    public function toLimitSql()
    {
        if (count($this->_limit) > 0) {
            return "LIMIT {$this->_limit['offset']}, {$this->_limit['count']} ";
        }
        return '';
    }

    /**
     * Renders the complete SQL statement for the query
     *
     * @access public
     * @return string
     */
    public function toSql()
    {
        $sql = $this->toSelectSql() .
            $this->toFromSql() .
            $this->toJoinSql() .
            $this->toWhereSql() .
            $this->toGroupSql() .
            $this->toOrderSql() .
            $this->toLimitSql();

        return trim($sql);
    }
}