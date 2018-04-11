<?php
  namespace prosys\model;
  
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;

  /**
   * Reprezentuje data access object vyrobce prideleneho partnerovi.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerProducerDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('partner_producers', PartnerProducerEntity::classname());
    }
    
    /**
     * Nacte vyrobce konkretniho partnera.
     * 
     * @param \prosys\model\UserEntity $partner
     * @return PartnerProducerEntity[]
     */
    public function loadByPartner(UserEntity $partner) {
      return $this->loadRecords(
        SqlFilter::create()->comparise('partner_id', '=', $partner->id)
      );
    }
    
    /**
     * Nacte vyrobce partnera dle partnera a id vyrobce.
     * 
     * @param \prosys\model\UserEntity $partner
     * @param int $producer
     * 
     * @return PartnerProducerEntity
     */
    public function loadByPartnerAndProducer(UserEntity $partner, $producer) {
      return Functions::first(
        $this->loadRecords(
          SqlFilter::create()->comparise('partner_id', '=', $partner->id)
                             ->andL()
                             ->comparise('producer_id', '=', $producer)
        )
      );
    }
  }
