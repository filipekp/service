<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu serie ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property SKCZ_ProducerEntity $producer element=id_vyrobce
   * @property string $nameCZ element=nazev
   * @property string $nameSK element=nazev_sk
   * @property bool $deleted element=odstranen&delete_flag
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_SerieEntity extends Entity
  {
  }
