<?php
  namespace prosys\model;
  
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;

  /**
   * Reprezentuje data access object pristupu partnera ke sluzbe.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerAccessDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('partner_accesses', PartnerAccessEntity::classname());
    }
    
    /**
     * Nacte posledni pristup partnera.
     * 
     * @param \prosys\model\UserEntity $partner
     * @return PartnerAccessEntity
     */
    public function loadLastAccess(UserEntity $partner) {
      return Functions::first(
        $this->loadRecords(
          SqlFilter::create()->comparise('partner_id', '=', $partner->id),
          [['column' => 'access_at', 'direction' => 'DESC']],
          [0, 1]
        )
      );
    }
    
    /**
     * Nacte log konkretniho partnera.
     * 
     * @param \prosys\model\UserEntity $partner
     * @return PartnerAccessEntity[]
     */
    public function loadByPartner(UserEntity $partner) {
      return $this->loadRecords(
        SqlFilter::create()->comparise('partner_id', '=', $partner->id)
      );
    }
  }
