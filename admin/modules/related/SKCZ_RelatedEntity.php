<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu souvisejiciho vyrobku ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property SKCZ_ProductEntity $product element=id_vyrobku
   * @property SKCZ_ProductEntity $related element=id_souvisejiciho
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_RelatedEntity extends Entity
  {
  }
