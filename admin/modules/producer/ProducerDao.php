<?php
  namespace prosys\model;

  /**
   * Reprezentuje data access object vyrobce.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ProducerDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('producers', ProducerEntity::classname());
    }
  }
