<?php
  namespace prosys\model;
  use prosys\core\mapper\SqlFilter;
  /**
   * Represents the menu data access object.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ModuleDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('modules', ModuleEntity::classname());
    }
  }
