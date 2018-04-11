<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu vyrobce prideleneho partnerovi.
   * 
   * @property int $id
   * @property UserEntity $partner element=partner_id
   * @property ProducerEntity $producer element=producer_id
   * @property float $profit
   * @property \DateTime $modifiedAt element=modified_at
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerProducerEntity extends Entity
  {
  }
