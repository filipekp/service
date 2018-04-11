<?php
  /***********************************************************
   *
   *  CLASS MySqlConnection
   *  class for MySQL database connection and work with
   *
   *  __construct($server, $user, $pass)
   *  __destruct()
   *  selectDb($db)
   *  execute($query)
   *  dbSelect($what, $from, $where, $order)
   *  fetchObjects($from = -1, $to = -1)
   *  fetchObject()
   *  rowCount($result = False)
   *  dbAggregation($what, $table, $where)
   *  dbInsert($into, $data)
   *  lastId()
   *  dbDelete($from, $where)
   *  dbUpdate($table, $what, $where)
   *
   *  getServer()
   *  setServer($server)
   *  getUser()
   *  setUser($user)
   *  getPassword()
   *  setPassword($pass)
   *  getDb()
   *  setDb($db)
   *  getResult()
   *  setResult($result)
   *
   ***********************************************************/

  class MySqlConnection {
    private $connection;

    private $server;
    private $user;
    private $pass;
    private $db;
    private $result;

    /*******************************************************
     * constructor - create a database connection
     *******************************************************/
    function __construct($server, $user = '', $pass = '', $db = '', $newLink = false) {
      $this->setServer($server);
      $this->setUser($user);
      $this->setPassword($pass);
      $this->setDb($db);

      $this->connection = @mysql_pconnect($server, $user, $pass, $newLink)
                          or die("Nelze se připojit");

      mysql_query("SET NAMES 'utf8'");

      mysql_select_db($db);
    }

    /*******************************************************
     * destructor - destroy object
     *******************************************************/
    function __destruct() {}

    /*******************************************************
     * method selectDb - choose database
     *
     * @param $db[string]   - database name
     *******************************************************/
    public function selectDb($db = '') {
      if ($db && $db != $this->db) {
        $this->setDb($db);
      }

      mysql_select_db($this->db);
    }

    /*******************************************************
     * method execute - execute sql query
     *
     * @param $query[string]  - query
     *
     * @return $resource[resource]    - mysql resource on success, or false on error
     *******************************************************/
    public function execute($query) {
      $this->setResult(mysql_query($query, $this->getConnection()));
  /*
      // TEST
      $wanted = 'D TRIM(`catalog_id`) = "';
      if (strpos($query, $wanted) !== False) {
        echo $query . '<br />';
        exit();
      }
  */
      return $this->getResult();
    }

    /*******************************************************
     * method dbSelect - database query SELECT
     *
     * @param   $what[string; array]
     * @param   $from[string]
     * @param   $where[string]
     *
     * @return  $object[mysql_object]
     *******************************************************/
    public function dbSelect($what, $from, $where = '', $order = '', $groupBy = '', $limit = '') {
      $query = "SELECT ";
      if (is_string($what)) {
        $query .= $what;
      } else {
        foreach ($what as $item) {
          $escape = strpos($item, '(') === false;
        	$query .= (($escape) ? '`' : '') . $item . (($escape) ? '`, ' : ', ');
        }
      }
      $query = rtrim($query, ", ");
      $query .= " FROM `" . $from . "`";

      $query .= (($where) ? ' WHERE ' . $where : '');
      $query .= (($groupBy) ? ' GROUP BY ' . $groupBy : '');
      $query .= (($order) ? ' ORDER BY ' . $order : '');
      $query .= (($limit) ? ' LIMIT ' . $limit : '');
  /*
  		// TEST
  		$wanted = 'service_description';
      if (strpos($query, $wanted) !== False) {
        echo $query . '<br />';
        exit();
  		}
  */
      return $this->execute($query);
    }

    /*******************************************************
     * method fetchObjects - fetch result to object array
     *
     * @param   $from[int]
     * @param   $to[int]
     *******************************************************/
    public function fetchObjects($from = -1, $to = -1) {
      $count = $this->rowCount();
    
      $limitBottom = (($from > -1) ? $from : 0);
      $limitTop = (($to > -1) ? min($to, $count) : $count);
      
      $result = array();
      if (@mysql_data_seek($this->getResult(), $limitBottom)) {
        while (!mysql_error($this->getConnection()) && $object = mysql_fetch_object($this->getResult())) {
          if ($limitBottom < $limitTop) {
            $result[] = $object;

            $limitBottom++;
          } else {
            break;
          }
        }
      }
      
      $this->setResult($result);
      return $this->getResult();
    }

    /*******************************************************
     * method fetchObject - fetch first result to object
     *******************************************************/
    public function fetchObject() {
      $this->fetchObjects(0, 1);
      if ($result = $this->getResult()) {
        $this->setResult($result[0]);
      }

      return $this->getResult();
    }
    
    /********************************************************
     * method rowCount - number of rows
     *
     * @param  $result[resource]
     *
     * @return $count[int]
     ********************************************************/
    public function rowCount($result = False) {
      $result = (($result === False) ? $this->getResult() : $result);
    
      return ((is_resource($result)) ? mysql_num_rows($result) : 0);
    }

    /********************************************************
     * method dbAggregation - database aggregate function
     *
     * @param  $aggregateFunction[string]
     * @param  $what[string]                  - item to aggregate
     * @param  $table[string]                 - table
     * @param  $where[string]                 - where
     *
     * @return $aggregation[mixed]
     ********************************************************/
  	function dbAggregation($aggregateFunction, $what, $table, $where = '') {
      $this->dbSelect($aggregateFunction . '(`' . $what . '`) AS `aggregation`', $table, $where);

      if (!mysql_error($this->getConnection()) && $data = mysql_fetch_object($this->getResult())) {
        return $data->aggregation;
      } else {
        return NULL;
  		}
  	}

    /*******************************************************
     * method dbInsert - database query INSERT
     *
     * @param $into[string]
     * @param $data[array]        array('column' => 'value', ...)
     *******************************************************/
    public function dbInsert($into, $data) {
      $query = "INSERT INTO `" . $into . "` (`" . implode('`, `', array_keys($data)) . '`) ' .
               'VALUES ("' . implode('", "', array_values($data)) . '")';
      $query = str_replace(array('"NOW()"', '"NULL"'), array('NOW()', 'NULL'), $query);
  /*
  		// TEST
  		$wanted = 'outlays';
      if (strpos($query, $wanted) !== False) {
        echo $query . '<br />';
        exit();
  		}
  */
      $this->execute($query);
    }

    /*******************************************************
     * method lastId - return last id of insert (specified by autoincrement column)
     *******************************************************/
    public function lastId() {
      return mysql_insert_id($this->getConnection());
    }

    /*******************************************************
     * method dbDelete - database query DELETE
     *
     * @param $from[string]
     * @param $where[string]
     *******************************************************/
    public function dbDelete($from, $where) {
      $query = "DELETE FROM `" . $from . "` WHERE " . $where;

      $this->execute($query);
    }

    /*******************************************************
     * method dbUpdate - database query UPDATE
     *
     * @param $table[string]
     * @param $what[array]        - ('attribute' => 'value', ... )
     * @param $where[string]
     *******************************************************/
    public function dbUpdate($table, $what, $where) {
      $query = 'UPDATE `' . $table . '` SET ';

      foreach ($what as $item) {
        $query .= '`' . key($what) . '` = ' . (($item == 'NOW()' || $item == 'NULL') ? $item : "'" . $item . "'") . ', ';

        next($what);
      }
      $query = rtrim($query, ", ");

      $query .= ' WHERE ' . $where;
/*
		// TEST
		$wanted = 'outlays';
    if (strpos($query, $wanted) !== False) {
      echo $query . '<br />';
      exit();
		}
*/
      $this->execute($query);
    }

    /*******************************************************
     * methods get/SLOT_NAME/   - return value of slot /SLOT_NAME/
     *******************************************************/
    public function getConnection() { return $this->connection; }
    public function getServer() { return $this->server; }
    public function getUser() { return $this->user; }
    public function getPassword() { return $this->pass; }
    public function getDb() { return $this->db; }
    public function getResult() { return $this->result; }

    /*******************************************************
     * methods set/SLOT_NAME/   - set value of slot /SLOT_NAME/
     * @param value
     *******************************************************/
    public function setConnection($connection) { $this->connection = $connection; }
    public function setServer($server) { $this->server = $server; }
    public function setUser($user) { $this->user = $user; }
    public function setPassword($pass) { $this->pass = $pass; }
    public function setDb($db) { $this->db = $db; }
    public function setResult($result) { $this->result = $result; }
  }
?>