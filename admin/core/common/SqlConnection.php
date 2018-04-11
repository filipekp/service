<?php
  namespace prosys\core\common;

  /**
   * Represents MySQL connection.
   * 
   * @property-read  string $server
   * @property-read  string $user
   * @property-read  string $password
   * @property-read  string $db
   * @property-read \PDO    $connection
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class SqlConnection implements \prosys\core\interfaces\IDataHandler
  {
    protected $connection;
    protected $result;

    protected $server;
    protected $user;
    protected $password;
    protected $db;
    protected $prefix;
    
    protected $_last_execution = 0;
    protected $_wait_timeout;
    
    abstract protected function connect();

    /**
     * Creates MySQL connection.
     * 
     * @param string $server
     * @param string $user
     * @param string $password
     * @param string $db
     * @param string $prefix
     * @param bool $newLink
     */
    public function __construct($server, $user = '', $password = '', $db = '', $prefix = '') {
      $this->server = $server;
      $this->user = $user;
      $this->password = $password;
      $this->db = $db;
      $this->prefix = $prefix;
      
      $this->connect();
      $this->_wait_timeout = $this->showVariable('wait_timeout');
    }
    
    /**
     * Obnovi pripojeni k databazi.
     * @return \PDO
     */
    public function reconnect() {
      unset($this->connection);
      return $this->connect();
    }
    
    /**
     * Prepares statement query and throws an error when necessary.
     * 
     * @param string $queryStmt
     * @param array $options
     * 
     * @return \PDOStatement
     * @throws AppException when statement query is wrong
     */
    protected function prepare($queryStmt, array $options = array()) {
      $queryStmt = str_replace('_PREFIX_', $this->prefix, $queryStmt);

      try {
        $now = microtime(TRUE);
        if ($this->_last_execution && (($now - $this->_last_execution) >= ($this->_wait_timeout * 0.9))) {
          $this->reconnect();
        }
        
        return $this->connection->prepare($queryStmt, $options);
      } catch (\PDOException $e) {
        throw new AppException(array('Wrong query statement: ' . $queryStmt, $e->getMessage()));
      }
    }

    /**
     * Executes the statement and stores the result statement into the $result property.
     * 
     * @param \PDOStatement $stmt
     * @return \PDOStatement
     * 
     * @throws AppException when execution fails
     */
    protected function execute(\PDOStatement $stmt) {
      try {
        $stmt->execute();
        $this->_last_execution = microtime(TRUE);
        
        $this->result = $stmt;
        return $this->result;
      } catch (\PDOException $e) {
        throw new AppException('Query execution failed: ' . $e->getMessage());
      }
    }
    
    /**
     * Binds prepared params (question mark type) to values.
     * 
     * @param \PDOStatement $stmt
     * @param array $data
     */
    protected function bindParams(\PDOStatement $stmt, array $data) {
      if ($data) {
        foreach ($data as $key => &$value) {
          if ($value == 'NULL') {
            $value = NULL;
            $type = \PDO::PARAM_NULL;
          } else {
            $type = \PDO::PARAM_STR;
          }

          $stmt->bindParam($key + 1, $value, $type);
        }
      }
    }
    
    /**
     * Binds prepared params (question mark type) to values.
     * 
     * @param \PDOStatement $stmt
     * @param array $condition SQL condition for prepared stmt => array('where' => 'col1 = ? AND col2 LIKE ?', 'bindings' => array(1, '%te%'))
     */
    protected function bindWhereParams(\PDOStatement $stmt, array $condition) {
      if ($condition) {
        $this->bindParams($stmt, $condition['bindings']);
      }
    }

    /**
     * Performs SELECT query and stores the result statement into the $result property.
     * 
     * @param string|array $what columns, which should be selected
     * @param string $from table name
     * @param array $condition SQL condition for prepared stmt => array('where' => 'col1 = ? AND col2 LIKE ?', 'bindings' => array(1, '%te%'))
     * @param string $order
     * @param string $groupBy
     * @param string $limit
     * 
     * @return resource|bool
     */
    public function select($what, $from, array $condition = array(), $order = '', $groupBy = '', $limit = '') {
      $where = (($condition) ? ' WHERE ' . $condition['where'] : '');
      $groupBy = (($groupBy) ? ' GROUP BY ' . $groupBy : '');
      $order = (($order) ? ' ORDER BY ' . $order : '');
      
      $query = 'SELECT ' . ((is_string($what)) ? $what : implode(', ', $what)) . 
               " FROM _PREFIX_{$from}{$where}{$groupBy}{$order}{$limit}";

      $stmt = $this->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
      $this->bindWhereParams($stmt, $condition);
      
      return $this->execute($stmt);
    }

    /**
     * Fetches result into the array of objects.
     * 
     * @param int $from
     * @param int $to
     * 
     * @return array
     */
    public function fetchObjects($from = -1, $to = -1) {
      $count = $this->rowCount();
    
      $limitBottom = (($from > -1) ? $from : 0);
      $limitTop = (($to > -1) ? min($to, $count) : $count);
      
      if ($limitBottom == 0 && $limitTop == $count) {
        $result = $this->result->fetchAll(\PDO::FETCH_OBJ);
      } else {
        $result = array();
        while (($row = $this->result->fetch(\PDO::FETCH_OBJ, \PDO::FETCH_ORI_ABS, $limitBottom)) && ($limitBottom < $limitTop)) {
          $result[] = $row;
          $limitBottom++;
        }
      }
      
      $this->result = $result;
      return $this->result;
    }

    /**
     * Fetches the first object of the resource.
     * 
     * @return object
     */
    public function fetchObject() {
      $this->result = $this->result->fetch(\PDO::FETCH_OBJ, \PDO::FETCH_ORI_FIRST);
      return $this->result;
    }

    /**
     * Gets the number of rows.
     * 
     * @param \PDOStatement $stmt
     * @return int
     */
    public function rowCount($stmt = NULL) {
      $obj = ((is_null($stmt)) ? $this->result : $stmt);
      if ($obj instanceof \PDOStatement) {
        return $obj->rowCount();
      }

      return 0;
    }

    /**
     * Performs aggregate function.
     * 
     * @param string $aggregateFunction
     * @param string $what
     * @param string $table
     * @param array  $condition SQL condition for prepared stmt => array('where' => 'col1 = ? AND col2 LIKE ?', 'bindings' => array(1, '%te%'))
     * 
     * @return string|NULL
     */
  	public function aggregate($aggregateFunction, $what, $from, array $condition = array()) {
      $what = (($what == '*') ? '*' : "{$what}");
      $this->select($aggregateFunction . '(' . $what . ') AS aggregation', $from, $condition);
      $this->fetchObject();
      
      return (($this->result) ? $this->result->aggregation : NULL);
  	}

    /**
     * Performs MySQL INSERT query.
     * 
     * @param string  $into table name
     * @param array   $data array('column' => 'value', ...)
     * @param bool    $ignoreStatus
     * 
     * @return int inserted id
     */
    public function insert($into, $data, $ignoreStatus = FALSE) {
      // query
      $ignore = (($ignoreStatus) ? ' IGNORE' : '');
      $query = "INSERT{$ignore} INTO _PREFIX_{$into} (" . implode(', ', array_keys($data)) . ') ' .
               'VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
      
      $stmt = $this->prepare($query);
      $this->bindParams($stmt, array_values($data));
      
      $this->execute($stmt);
      return $this->insertedId();
    }

    /**
     * Performs MySQL DELETE query.
     * 
     * @param string $from
     * @param array $condition SQL condition for prepared stmt => array('where' => 'col1 = ? AND col2 LIKE ?', 'bindings' => array(1, '%te%'))
     * 
     * @return bool
     */
    public function delete($from, array $condition) {
      if ($condition) {
        $stmt = $this->prepare("DELETE FROM _PREFIX_{$from} WHERE {$condition['where']}");
        $this->bindWhereParams($stmt, $condition);
        
        return $this->execute($stmt);
      } else {
        throw new AppException('DELETE query has no condition.');
      }
    }

    /**
     * Performs MySQL UPDATE query.
     * 
     * @param string $table
     * @param array $data array('column' => 'value', ...)
     * @param array $condition SQL condition for prepared stmt => array('where' => 'col1 = ? AND col2 LIKE ?', 'bindings' => array(1, '%te%'))
     * 
     * @return bool
     */
    public function update($table, $data, array $condition) {
      // escape data and prepare to update
      $prepared = $data;
      array_walk($prepared, function(&$value, $column) {
        $value = "{$column} = ?";
      });

      // create prepared statement and bind params of data to set and bind params of where condition -> !WARNING -> the order is essential
      $stmt = $this->prepare("UPDATE _PREFIX_{$table} SET " . implode(', ', $prepared) . " WHERE {$condition['where']}");
      $this->bindParams($stmt, array_merge(array_values($data), $condition['bindings']));

      return $this->execute($stmt);
    }
    
    /**
     * Retrieves the ID generated for an AUTO_INCREMENT column by the previous query.
     * 
     * @return int
     */
    public function insertedId() {
      $lastId = (int)$this->connection->lastInsertId();
      return (($lastId === 0) ? TRUE : $lastId);
    }
    
    /**
     * Vycisti pamet od aktualniho vysledku.
     */
    public function clearResult() {
      if (is_array($this->result)) {
        foreach ($this->result as $idx => $value) {
          $this->result[$idx] = NULL;
        }
      }

      $this->result = NULL;
    }
    
    /**
     * Vrati hodnotu parametru SQL databaze.
     * 
     * @param \PDOStatement $variable
     * @return mixed
     */
    public function showVariable($variable) {
      $query = "SHOW VARIABLES LIKE '$variable'";
      $stmt = $this->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
      
      $this->execute($stmt);
      $this->fetchObject();

      return (($this->result) ? $this->result->Value : NULL);
    }

    /********************************* GETTERS *********************************/

    public function getServer() {
      return $this->server;
    }

    public function getUser() {
      return $this->user;
    }

    public function getPassword() {
      return $this->password;
    }

    public function getDb() {
      return $this->db;
    }
    
    public function getPrefix() {
      return $this->prefix;
    }

    public function getResult() {
      return $this->result;
    }
  }
