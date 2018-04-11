<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity partnera ze serveru StyloveKoupelny.cz (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_PartnerDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                     Settings::SKCZ_DB_USER,
                                                                                     Settings::SKCZ_DB_PASSWORD,
                                                                                     Settings::SKCZ_DB_DATABASE,
                                                                                     Settings::SKCZ_DB_PREFIX),
                                       'SKCZ_MySqlConnection');
      parent::__construct(SKCZ_PartnerEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'firmy');
    }
    
    /**
     * Vrati partnery dle casti jejich jmena.
     * 
     * @param type $term
     * @return SKCZ_PartnerEntity[]
     */
    public function loadByTerm($term) {
      return $this->loadRecords(
        SqlFilter::create()->contains('nazev', $term)
      );
    }
  }
