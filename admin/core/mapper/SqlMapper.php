<?php
  namespace prosys\core\mapper;
  
  use prosys\core\interfaces\IFilterable,
      prosys\core\common\Functions;

  abstract class SqlMapper implements \prosys\core\interfaces\IMapper
  {
    /** @var \prosys\core\interfaces\IDataHandler */
    protected $_dataHandler;

    protected $_table;
    protected $_primaryKeyElement;
    
    /**
     * Initializes the MySQL connection.
     * 
     * @param \prosys\core\interfaces\IDataHandler $dataHandler
     * @param string $table
     * @param string $primaryKeyElement
     */
    public function __construct(\prosys\core\interfaces\IDataHandler $dataHandler, $table, $primaryKeyElement) {
      $this->_dataHandler = $dataHandler;
      $this->_table = $table;
      $this->_primaryKeyElement = $primaryKeyElement;
    }
    
    /**
     * Obnovi pripojeni k databazi.
     * @return \PDO
     */
    public function reconnect() {
      return $this->_dataHandler->reconnect();
    }
    
    /**
     * Returns database condition of primary key.
     * @return array
     */
    protected function getPrimaryKeyCondition($primaryKeyValue) {
      return array(
        'where' => "{$this->_primaryKeyElement} = ?",
        'bindings' => array($primaryKeyValue)
      );
    }

    /**
     * Finds the record by primary key value or closer specification.
     * 
     * @param mixed $arg should be primary key value or IFilterable
     * @return array
     */
    public function find($arg) {
      if ($arg instanceof IFilterable) {
        $condition = $arg->resultFilter();
      } else {
        $condition = $this->getPrimaryKeyCondition($arg);
      }
      
      $this->_dataHandler->select('*', $this->_table, $condition);
      return $this->_dataHandler->fetchObject();
    }

    /**
     * Inserts the record into the storage.<br />
     * If the insertion was correct, the inserted id will be set into the entity.
     * 
     * @param array $data
     * @return mixed|bool primary key value or bool
     */
    public function insert(array $data) {
      return $this->_dataHandler->insert($this->_table, $data);
    }

    /**
     * Updates the record into the storage.
     * 
     * @param array $data
     * @return bool
     */
    public function update(array $data) {
      return $this->_dataHandler->update($this->_table, $data, $this->getPrimaryKeyCondition($data[$this->_primaryKeyElement]));
    }
    
    /**
     * Deletes the record from the storage according to given primary key value or Entity itself.
     * 
     * @param mixed $primaryKeyValue
     * @return bool
     */
    public function delete($primaryKeyValue) {
      return $this->_dataHandler->delete($this->_table, $this->getPrimaryKeyCondition($primaryKeyValue));
    }
    
    /**
     * This function serialiize orders.
     * 
     * @param array $orders
     * @return string
     */
    protected function serializeOrder($orders) {
      $res = array();

      foreach ($orders as $order) {
        $dir = ' ' . ((array_key_exists('direction', $order)) ? strtoupper($order['direction']) : 'ASC');
        if (is_array($order['column'])) {         
          $res[] = 'CONCAT(`' . implode(', ', $order['column']) . ')' . $dir;
        } else {
          $res[] = $order['column'] . $dir;
        }   
      }

      return implode(', ', $res);
    }

    /**
     * Finds the records by specification.
     * 
     * @param prosys\core\interfaces\IFilterable $filter
     * @param array $order order by
     * @param array $limit limit
     * 
     * @return array
     */
    public function findRecords(IFilterable $filter = NULL, array $order = array(), array $limit = array()) {
      $this->_dataHandler->select('*',                                                        // what
                                  $this->_table,                                              // table name
                                  ((is_null($filter)) ? array() : $filter->resultFilter()),   // where condition
                                  $this->serializeOrder($order),                              // order by
                                  '',                                                         // group by
                                  (($limit) ? $limit[0] . ', ' . $limit[1] : ''));            // limit
      
      return $this->_dataHandler->fetchObjects();
    }
    
    /**
     * Vrati specifikované sloupce vyfiltrovaných řádků.
     * 
     * @param array $columns
     * @param prosys\core\interfaces\IFilterable $filter
     * @param array $order order by
     * @param array $limit limit
     * 
     * @return array
     */
    public function findRecordsProjection(array $columns, IFilterable $filter = NULL, array $order = array(), array $limit = array()) {
      $this->_dataHandler->select(implode(', ', $columns),                                                        // what
                                  $this->_table,                                              // table name
                                  ((is_null($filter)) ? array() : $filter->resultFilter()),   // where condition
                                  $this->serializeOrder($order),                              // order by
                                  '',                                                         // group by
                                  (($limit) ? $limit[0] . ', ' . $limit[1] : ''));            // limit
      
      return $this->_dataHandler->fetchObjects();
    }
    
    /**
     * Deletes the records specified by filter.
     * 
     * @param prosys\core\interfaces\IFilterable $filter
     */
    public function deleteRecords(IFilterable $filter) {
      return $this->_dataHandler->delete($this->_table, $filter->resultFilter());
    }
    
    /**
     * Find max value by column.
     * 
     * @param string $type
     * @param string $element
     * @param prosys\core\interfaces\IFilterable $filter
     * 
     * @return mixed
     */
    public function max($type, $column, IFilterable $filter) {
      return Functions::retype($this->_dataHandler->aggregate('MAX', $column, $this->_table, $filter->resultFilter()), $type);
    }
    
    /**
     * Find average value of column.
     * 
     * @param string $type type of result (int, float, ...)
     * @param string $element
     * @param prosys\core\interfaces\IFilterable $filter
     * 
     * @return mixed
     */
    public function avg($type, $column, IFilterable $filter) {
      return Functions::retype($this->_dataHandler->aggregate('AVG', $column, $this->_table, $filter->resultFilter()), $type);
    }
    
    /**
     * Find sum value of column.
     * 
     * @param string $type type of result (int, float, ...)
     * @param string $what condition (`price` * `quantity`)
     * @param prosys\core\interfaces\IFilterable $filter
     * 
     * @return mixed
     */
    public function sum($type, $what, IFilterable $filter) {
      return Functions::retype($this->_dataHandler->aggregate('SUM', $what, $this->_table, $filter->resultFilter()), $type);
    }
    
    /**
     * Get count of results select.
     *  
     * @param string $element
     * @param prosys\core\interfaces\IFilterable $filter
     * 
     * @return mixed
     */
    public function count($column, IFilterable $filter = NULL) {
      return Functions::retype(
        $this->_dataHandler->aggregate('COUNT', $column, $this->_table, ((is_null($filter)) ? array() : $filter->resultFilter())),
        'int'
      );
    }
    
    /**
     * Gets list of grouped elements count.
     * 
     * @param string $groupColumn
     * @param string $countColumn
     * @param \prosys\core\interfaces\IFilterable $filter
     * 
     * @return type
     */
    public function groupCount($groupColumn, $countColumn, IFilterable $filter = NULL) {
      $this->_dataHandler->select(
        "{$groupColumn}, COUNT({$countColumn}) AS count",       // what
        $this->_table,                                                // table name
        ((is_null($filter)) ? array() : $filter->resultFilter()),     // where condition
        '',                                                           // order by
        "{$groupColumn}"                                            // group by
      );
      
      return $this->_dataHandler->fetchObjects();  
    }
    
    /**
     * Vrati vysledek volani ulozene funkce SQL.
     * 
     * @param string $function
     * @param array $params
     * 
     * @return mixed
     */
    public function callFunction($function, array $params) {
      $params = implode(', ', $params);
      $this->_dataHandler->select("{$function}({$params}) AS result", 'DUAL');

      return $this->_dataHandler->fetchObject()->result;
    }
    
    /**
     * Getter.
     * @return string
     */
    public function getTable() {
      return $this->_table;
    }
    
    /**
     * Vrati jedinečné hodnoty pro sloupec a podle filtru.
     * 
     * @param string $column
     * @param \prosys\core\interfaces\IFilterable $filter
     * @param string $direction optional: ASC (default) | DESC
     * 
     * @return array
     */
    public function distinct($column, IFilterable $filter = NULL, $direction = 'ASC') {
      return $this->findRecordsProjection(array('DISTINCT(' . $column . ') as ' . $column), $filter, array(array('column' => $column, 'direction' => $direction)));
    }
    
    /**
     * Vycisti pamet od aktualniho vysledku.
     */
    public function clearResult() {
      $this->_dataHandler->clearResult();
    }
  }
