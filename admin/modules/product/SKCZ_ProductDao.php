<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity vyrobku ze serveru StyloveKoupelny.cz (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_ProductDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                     Settings::SKCZ_DB_USER,
                                                                                     Settings::SKCZ_DB_PASSWORD,
                                                                                     Settings::SKCZ_DB_DATABASE,
                                                                                     Settings::SKCZ_DB_PREFIX),
                                       'SKCZ_MySqlConnection');
      parent::__construct(SKCZ_ProductEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'vyrobky');
    }
    
    /**
     * Zjisti partnerovu nejlepsi cenu vyrobku.
     * 
     * @param \prosys\model\SKCZ_ProductEntity $product
     * @param \prosys\model\PartnerEntity $partner
     * 
     * @return float
     */
    public function getPrice(SKCZ_ProductEntity $product, PartnerEntity $partner) {
      $suffix = (($partner->styleplusPartner->engine == 'SK') ? 'SK' : '');
      return (float)$this->_mapper->callFunction('cenaNejlepsi' . $suffix,
        [$partner->styleplusPartner->id, -1, $product->producer->id, $product->id, 1]
      );
    }
  }
