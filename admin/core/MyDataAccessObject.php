<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;

  /**
   * Abstract class which should be the the object to access entity data.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class MyDataAccessObject extends SQLDataAccessObject
  {
    /**
     * Initializes MySQL DAO.
     * 
     * @param string $table
     * @param string $entityClass
     */
    public function __construct($table, $entityClass) {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::DB_SERVER,
                                                                                     Settings::DB_USER,
                                                                                     Settings::DB_PASSWORD,
                                                                                     Settings::DB_DATABASE,
                                                                                     Settings::DB_PREFIX));
      
      parent::__construct($entityClass, 'prosys\core\mapper\MySqlMapper', $mySqlHandler, $table);
    }
  }
