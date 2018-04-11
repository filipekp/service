<?php
  namespace prosys\model;

  /**
   * Represents the entity of group right.
   * 
   * @property int $id
   * @property GroupEntity $group element=group_id
   * @property ModuleActionEntity $action element=module_action
   * @property string $allowedQueries element=allowed_queries
   * @property bool $isUncheckable element=is_uncheckable
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class GroupRightEntity extends Entity
  {
  }
