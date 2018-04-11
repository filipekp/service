<?php
  namespace prosys\model;

  /**
   * Abstract class which should be the the object to access entity data.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class SQLDataAccessObject extends DataAccessObject
  {
    /**
     * Obnovi pripojeni k databazi.
     * @return \PDO
     */
    public function reconnect() {
      return $this->_mapper->reconnect();
    }
  }
