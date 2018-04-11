<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu kategorie ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property SKCZ_CategoryEntity $parent element=id_rodice
   * @property string $nameCZ element=nazev
   * @property string $nameSK element=nazev_sk
   * @property int $sequence element=poradi
   * @property bool $deleted element=odstranen&delete_flag
   * 
   * @property SKCZ_CategoryEntity[] $children binding=1n:>id_rodice:poradi>
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_CategoryEntity extends Entity
  {
  }
