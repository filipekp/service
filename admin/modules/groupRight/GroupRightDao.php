<?php
  namespace prosys\model;
  
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;

  /**
   * Represents the group right data access object.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class GroupRightDao extends MyDataAccessObject
  {
    public function __construct()
    {
      parent::__construct('group_rights', GroupRightEntity::classname());
    }
    
    /**
     * Get rigt by group and module action id.
     * 
     * @param GroupEntity $group
     * @param int $moduleAction
     * 
     * @return GroupRightEntity
     */
    public function loadRightByGroupAndModuleAction($group, $moduleAction) {
      $groupRight = Functions::first($this->loadRecords(
        SqlFilter::create()
          ->comparise('group_id', '=', $group->id)
          ->andL()->comparise('module_action', '=', $moduleAction)));
      return (($groupRight) ? $groupRight : $this->load());
    }
    
    /**
     * Zjisti pravo skupiny podle skupin uzivatelu, modulu a akce.
     * 
     * @param array $userGroups
     * @param string $module
     * @param string $action
     * 
     * @return GroupRightEntity
     */
    public function loadGroupRightByGroupsModuleAndAction($userGroups, $module, $action) {
      return Functions::first($this->loadRecords(
        SqlFilter::create()
          ->inArray('group_id', $userGroups)
          ->andL()->inFilter('module_action',
            SqlFilter::create()
              ->filter('id', 'module_actions',
                SqlFilter::create()
                  ->comparise('module', '=', $module)
                  ->andL()->comparise('action_name', '=', $action)))));
    }
  }
