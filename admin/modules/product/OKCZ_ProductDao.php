<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity vyrobku ze serveru OnlineKoupelny.cz (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class OKCZ_ProductDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::OKCZ_DB_SERVER,
                                                                                     Settings::OKCZ_DB_USER,
                                                                                     Settings::OKCZ_DB_PASSWORD,
                                                                                     Settings::OKCZ_DB_DATABASE,
                                                                                     Settings::OKCZ_DB_PREFIX),
                                       'OKCZ_MySqlConnection');
      parent::__construct(OKCZ_ProductEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'swe_shop_items');
    }
  }
