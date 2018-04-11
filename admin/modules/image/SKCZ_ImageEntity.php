<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu obrazku vyrobku ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property SKCZ_ProductEntity $product element=id_vyrobku
   * @property string $smallType element=mala_typ
   * @property string $bigType element=velka_typ
   * @property string $originalType element=original_typ
   * @property \DateTime $modification element=upraven
   * @property bool $deleted element=odstranen&delete_flag
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_ImageEntity extends Entity
  {
  }
