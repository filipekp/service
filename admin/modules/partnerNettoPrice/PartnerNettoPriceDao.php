<?php
  namespace prosys\model;
  
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;

  /**   
   */
  class PartnerNettoPriceDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('partner_prices', PartnerNettoPriceEntity::classname());
    }
        
  }
