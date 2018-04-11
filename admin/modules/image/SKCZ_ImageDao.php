<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity obrazku vyrobku ze serveru StyloveKoupelny.cz (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_ImageDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                     Settings::SKCZ_DB_USER,
                                                                                     Settings::SKCZ_DB_PASSWORD,
                                                                                     Settings::SKCZ_DB_DATABASE,
                                                                                     Settings::SKCZ_DB_PREFIX),
                                       'SKCZ_MySqlConnection');
      parent::__construct(SKCZ_ImageEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'vyrobky_obrazky');
    }
  }
