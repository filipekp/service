<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu vyrobce ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property string $code element=kod_s3
   * @property string $name element=nazev
   * @property int $sortOrder element=poradi
   * @property bool $noshop element=mimo_shop
   * @property bool $hidden element=nezobrazovat
   * @property bool $deleted element=odstranen&delete_flag
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_ProducerEntity extends Entity
  {
  }
