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
  class MySqlConnection extends SqlConnection
  {
    /**
     * Vytvori db spojeni s MySQL.
     * 
     * @return \PDO
     * @throws AppException
     */
    protected function connect() {
      try {
        $options = array(
          \PDO::ATTR_PERSISTENT         => TRUE,
          \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_EMULATE_PREPARES   => FALSE,
          \PDO::MYSQL_ATTR_FOUND_ROWS   => TRUE,
          \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE'
        );
				
        $this->connection = new \PDO("mysql:host={$this->server};dbname={$this->db};charset=utf8", $this->user, $this->password, $options);
        $this->connection->query('SET SESSION group_concat_max_len = 4096');
      } catch (\PDOException $e) {
        throw new AppException('Connection failed: ' . $e->getMessage());
      }
      
      return $this->connection;
    }

    /**
     * Performs SELECT query and stores the result statement into the $result property.
     * 
     * @param string|array $what columns, which should be selected
     * @param string $from table name
     * @param array $condition SQL condition for prepared stmt => array('where' => '`col1` = ? AND `col2` LIKE ?', 'bindings' => array(1, '%te%'))
     * @param string $order
     * @param string $groupBy
     * @param string $limit
     * 
     * @return resource|bool
     */
    public function select($what, $from, array $condition = array(), $order = '', $groupBy = '', $limit = '') {
      $from = (($from == 'DUAL') ? $from : "`_PREFIX_{$from}`");
      $where = (($condition) ? ' WHERE ' . $condition['where'] : '');
      $groupBy = (($groupBy) ? ' GROUP BY ' . $groupBy : '');
      $order = (($order) ? ' ORDER BY ' . $order : '');
      $limit = (($limit) ? ' LIMIT ' . $limit : '');
      
      $query = 'SELECT ' . ((is_string($what)) ? $what : '`' . implode('`, `', $what) . '`') . 
               " FROM {$from}{$where}{$groupBy}{$order}{$limit}";
               
//      if (strpos($query, 'bestPrice') !== FALSE) {
//        var_dump($query); exit();
//      }

      $stmt = $this->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
      $this->bindWhereParams($stmt, $condition);
      
      return $this->execute($stmt);
    }
    
    /**
     * Performs CALL query of procedure which calls SELECT query on its end.
     * 
     * @param string $procedure
     * @param array $params
     * 
     * @return resource|bool
     */
    public function callProcedureSelect($procedure, array $params = array()) {
      $stmt = $this->prepare(
        "CALL {$procedure}(" . implode(', ', array_fill(0, count($params), '?')) . ")",
        array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
      );

      $this->bindParams($stmt, $params);
      
      return $this->execute($stmt);
    }
  }
