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
  class MSSqlConnection extends SqlConnection
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

        $this->connection = new \PDO("sqlsrv:Server={$this->server};Database={$this->db};charset=utf8", $this->user, $this->password, $options);
      } catch (\PDOException $e) {
        throw new AppException('Connection failed: ' . $e->getMessage());
      }
      
      return $this->connection;
    }
  }
