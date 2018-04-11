<?php
  namespace prosys\core\interfaces;

  /**
   * Represents the data handleable interface.<br />
   * Notice: designed for MySQL Connection, but it should be fully functional for any type of the data handling.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IDataHandler
  {
    /**
     * Selects data according to the parameters.
     */
    public function select($what, $from, array $condition = array(), $order = '', $groupBy = '', $limit = '');
    
    /**
     * Fetches limited objects into the result (result available through the getResult() method).
     */
    public function fetchObjects($from = -1, $to = -1);
    
    /**
     * Fetches the first object into the result (result available through the getResult() method).
     */
    public function fetchObject();
    
    /**
     * Returns the result of last fetch.
     */
    public function getResult();
    
    /**
     * Gets number of last result rows (or given result set).
     */
    public function rowCount($stmt = NULL);
    
    /**
     * Implements simple aggregating functions (as in the SQL).
     */
    public function aggregate($aggregateFunction, $what, $from, array $condition = array());
    
    /**
     * Inserts new data.
     */
    public function insert($into, $data);
    
    /**
     * Deletes data.
     */
    public function delete($from, array $condition);
    
    /**
     * Updates data.
     */
    public function update($where, $data, array $condition);
    
    /**
     * Returns identifier of the last inserted data.
     */
    public function insertedId();
    
    public function clearResult();
  }
