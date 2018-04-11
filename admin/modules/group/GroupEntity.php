<?php
  namespace prosys\model;

  /**
   * Represents the entity of group.
   * 
   * @property int $id
   * @property string $name
   * 
   * @property GroupRightEntity[] $rights binding=1n:>:>
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class GroupEntity extends Entity
  {
  }
