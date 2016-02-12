<?php
namespace Oxide\Data;

use Exception;
use Oxide\Common\Pattern\SharedInstanceTrait;
use PDO;

/**
 * Database Connection class
 *
 * Connection is based on PDO extension.  This extension must be loaded before using this class.
 *
 * @package oxide
 * @subpackage data
 * @author Alamin Ahmed <aahmed753@gmail.com>
 */
class Connection
{
    use SharedInstanceTrait;

    /**
     *
     * @access protected
     * @var type
     */
    protected
        $_pdo = null,
        $_config = [],
        $_options = [],
        $_dsn = '';

    const
        CNF_CATALOG = 'catalog',
        CNF_DRIVER = 'driver',
        CNF_HOST = 'host',
        OP_EQ = '=',
        OP_NOT_EQ = '<>',
        OP_GREATER_EQ = '>=',
        OP_LESS_EQ = '<=',
        OP_GREATER = '>',
        OP_LESS = '<',
        OP_IN = 'IN',
        OP_AND = 'AND',
        OP_OR = 'OR',
        OP_LIKE = 'LIKE',
        OP_NULL = 'NULL',
        OP_NONE = 'NONE',
        JOIN_LEFT = 'LEFT JOIN',
        JOIN_RIGHT = 'RIGHT JOIN',
        JOIN_INNER = 'INNER JOIN',
        JOIN_OUTER_LEFT = 'LEFT OUTER JOIN',
        SESSION_KEY_LAST_QUERY_SQL = '__oxide_data_Connection:lastquerysql',
        SESSION_KEY_LAST_QUERY_PARAM = '__oxide_data_Connection:lastqueryparam',
        SESSION_KEY_LAST_QUERY_COUNT = '__oxide_data_Connection:lastquerycount';

    /**
     * construction
     *
     * takes an associative array with all required keys with value.
     * following keys are required:
     *    driver: name of the driver.
     *    host: server host name or IP address
     *    catalog: database catalog name
     *    username: login username
     *    password: password
     * all additional options for PDO must be provided separately in second params.
     *
     * @todo fix dsn for other drivers, or put dsn string on ini file
     * @access public
     * @param $config array
     * @param $options array
     */
    public function __construct(array $config, array $options = null)
    {
        // storing the config array locally.
        // _connect() function will use this to perform actual connection
        $this->_config = $config;
        $this->_options = $options;
    }

    /**
     * returns internal PDO instant
     *
     * @access public
     * @return \PDO
     */
    public function getPDO()
    {
        if (!$this->_pdo) {
            $this->connect();
        }

        return $this->_pdo;
    }


    /**
     * Get the connection host name
     *
     * This information should be provided by the $config
     * @access public
     * @return string
     */
    public function getHostName()
    {
        if (!isset($this->_config[self::CNF_HOST])) {
            return null;
        }
        return $this->_config[self::CNF_HOST];
    }


    /**
     * Get the catalog name.
     *
     * @access public
     * @return void
     */
    public function getCatalogName()
    {
        if (!isset($this->_config[self::CNF_CATALOG])) {
            return null;
        }
        return $this->_config[self::CNF_CATALOG];
    }


    /**
     * Get the driver name.
     *
     * @access public
     * @return void
     */
    public function getDriverName()
    {
        if (!isset($this->_config[self::CNF_DRIVER])) {
            return null;
        }
        return $this->_config[self::CNF_DRIVER];
    }

    /**
     * generate dsn string
     *
     * checks for all dsn required config values and generates pdo dsn string
     * @access private
     * @return string
     */
    private function _generateDSN()
    {
        $driver = $this->getDriverName();
        $host = $this->getHostName();
        $catalog = $this->getCatalogName();

        if (!$driver || !$host || !$catalog) {
            throw new \Exception('Driver, host or catalog name is not found.  Check your config.');
        }

        // generating the dsn string
        $this->_dsn = "{$driver}:host={$host};dbname={$catalog}";
        return $this->_dsn;
    }

    /**
     * Performs actual connection to the database.
     *
     * every other public function calls this function before perfoming action.
     * this function will check for active connection, if not attempts to connect.
     * @access private
     */
    public function connect()
    {
        if ($this->_pdo === null) {
            $dsn = $this->_generateDSN();
            $username = $this->_config['username'];
            $password = $this->_config['password'];
            $options = $this->_options;
            $this->_pdo = new \PDO($dsn, $username, $password, $options);

            // we want errors to through exception instead of classic error messages.
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // use our subclass of PDOStatement
            $this->_pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('oxide\data\Statement', array($this)));
        }
    }

    /**
     * sets PDO attributes.
     *
     * must use this method before performing any action in order to take advantage of the
     * new settings.
     * @access public
     * @param $attr int
     * @param $value mixed
     * @return bool
     */
    public function setAttribute($attr, $value)
    {
        return $this->getPDO()->setAttribute($attr, $value);
    }

    /**
     * prepares an sql statement for executing.
     *
     * use of this method is highly recommended.
     * @access public
     * @param $sql string
     * @return Statement
     */
    public function prepare($sql)
    {
        return $this->getPDO()->prepare($sql);
    }

    /**
     * Perform direct query to database.
     *
     * This method is discourage in favor of prepare/execute methods.
     *
     * This method can also take sql\Query object. In that case it will use execute
     * method of that object.
     * @access public
     * @param $sql
     * @return Statement
     */
    public function query($sql)
    {
        return $this->getPDO()->query($sql);
    }

    /**
     * Performs data manupulation query to database.
     *
     * This method is discourage in favor of prepare/execute methods.
     * @access public
     * @param $sql
     * @return int
     */
    public function exec($sql)
    {
        return $this->getPDO()->exec($sql);
    }


    /**
     * Quotes given string.
     *
     * Do not use this function when using prepare.  prepare will
     * do it's quoting and escaping automatically.
     * @access public
     * @param $str string
     * @return string
     */
    public function quote($str)
    {
        return $this->getPDO()->quote($str);
    }

    /**
     * returns last insert id if found
     *
     * use this method after executing INSERT statement.
     * the table where INSERT is performed, must have primary key column
     * @return int
     */
    public function lastInsertId()
    {
        return $this->getPDO()->lastInsertId();
    }

    /**
     * returns raw PDO error info
     *
     * @access public
     * @return array
     */
    public function errorInfo()
    {
        return $this->getPDO()->errorInfo();
    }

    /**
     *
     * @access public
     * @param type $table
     * @param type $param
     * @param type $smnt
     */
    public function select($table, $param, &$smnt = null)
    {
        if (array_key_exists('select', $param)) {
            //$select =
        }
    }

    /**
     * performs insert operation on specified database table
     * @access public
     * @param string $table
     * @param array $row must be an assoicative array
     * @return int
     */
    public function insert($table, $row, Statement &$smnt = null)
    {
        // prepare columns to be inserted
        $columns = implode(',', array_keys($row));
        $values = implode(',', array_fill(0, count($row), '?'));

        // reuse if statement is being maintained by the caller
        if (!$smnt) {
            // building the sql query
            $sql = "insert into {$table} ({$columns}) values ({$values})";

            // preparing the sql statement
            $smnt = $this->prepare($sql);
        }

        // execute
        $smnt->execute(array_values($row));

        // return
        return $this->lastInsertId();
    }

    /**
     * performs update operation on specified database table
     *
     * updates a given $table with given $row information based on given $where value.
     * @access public
     * @param string $table
     * @param array $row must be associative array
     * @param string $where sql where clause portion
     * @param array $binds additional parameters to be binded for where clause
     * @return Statement
     */
    public function update($table, $row, $where, array $binds = null, Statement &$smnt = null)
    {
        if (!is_array($where)) {
            $where = [$where];
        }

        // prepare columns to be updated
        foreach ($row as $key => $value) {
            /**
             * for null value, set special sql null keyword
             * binding param with PARAM_NULL is not working.
             * workaround is to manually set null keyword and unset the row from the binding
             */
            if (is_null($value) || strtolower($value) == 'null') {
                $set[] = "$key = null";
                unset($row[$key]);
            } else {
                $set[] = "$key = :{$key}";
            }
        }

        if (!$smnt) {
            // build the sql query
            $sql = "update {$table} set " . implode(',', $set) . " where " . implode(' AND ', $where);

            // prepare sql statement
            $smnt = $this->prepare($sql);
        }

        // execute
        $smnt->execute(array_merge($row, $binds));

        // return statement for
        return $smnt->rowCount();
    }

    /**
     * performs delete operation on specified database table
     * @access public
     * @param string $where
     * @param array $binds
     * @return Oxide_Db_Statement
     */
    public function delete($table, $where, array $binds = null, Statement &$smnt = null)
    {
        // reuse statement if maintained by the caller
        if (!$smnt) {
            // build the sql query
            $sql = "delete from {$table} where {$where}";

            // prepare the sql statement
            $smnt = $this->prepare($sql);
        }

        // execute
        $smnt->execute($binds);

        // return
        return $smnt->rowCount();
    }

    /**
     * returns an associative array of columns for a give $table.
     *
     * @note name of the column is the key of the array and type of the column is the value of the array
     * @param string $table
     * @return array
     */
    public function columnsForTable($table)
    {
        // performs database query
        $columns = $this->query("SHOW COLUMNS FROM {$table}")->fetchAll();
        $schema = array();

        // scan throw all columns
        foreach ($columns as $column) {
            // get the field name
            $name = $column['Field'];

            // determine field value type
            // and it's size or values
            if (preg_match("/([\w]+)\(([\w\W]+)\)/", $column['Type'], $matches)) {
                $type = $matches[1];
                $size = str_replace("'", '', $matches[2]);
            } else {
                $type = $column['Type'];
                $size = null;
            }

            $schema[$name] = $type;
        }

        // return the schema
        return $schema;
    }

    /**
     * disconnects from the database
     * @access public
     */
    public function close()
    {
        $this->_pdo = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}