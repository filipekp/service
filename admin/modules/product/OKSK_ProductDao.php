<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity vyrobku ze serveru OnlineKupelne.sk (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class OKSK_ProductDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::OKSK_DB_SERVER,
                                                                                     Settings::OKSK_DB_USER,
                                                                                     Settings::OKSK_DB_PASSWORD,
                                                                                     Settings::OKSK_DB_DATABASE,
                                                                                     Settings::OKSK_DB_PREFIX),
                                       'OKSK_MySqlConnection');
      parent::__construct(OKSK_ProductEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'swe_shop_items');
    }
  }
