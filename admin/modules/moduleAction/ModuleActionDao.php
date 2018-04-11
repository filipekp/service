<?php
  namespace prosys\model;
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions,
      prosys\core\common\Agents;
  /**
   * Represents the menu data access object.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ModuleActionDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('module_actions', ModuleActionEntity::classname());
    }
    
    public function loadByModuleAndAction($module, $action) {
      return Functions::first($this->loadRecords(
        SqlFilter::create()
          ->comparise('module', '=', $module)
          ->andL()->comparise('action_name', '=', $action)));
    }
    
    /**
     * Get children for mudule action.
     * @param ModuleActionEntity $parent
     * @return type
     */
    private function getChildren($parent) {
      return $this->loadRecords(
        SqlFilter::create()
          ->comparise('parent', '=', $parent->id),
        array(
          array('column' => 'module'),
          array('column' => 'action_type'),
          array('column' => 'action_title')
        )
      );
    }
    
    /**
     * 
     * @param ModuleActionEntity[] $moduleActions
     */
    private function prepareModuleActions($moduleActions) {
      $moduleActionsRes = array();
      foreach ($moduleActions as $moduleAction) {
        $moduleActionsRes[$moduleAction->id]['item'] = $moduleAction;
        $children = $this->getChildren($moduleAction);
        if ($children) {
          $moduleActionsRes[$moduleAction->id]['children'] = $this->prepareModuleActions($children);
        }
      }
      
      return $moduleActionsRes;
    }
    
    /**
     * 
     * @param ModuleEntity $module
     * @return type
     */
    public function getModuleActions($module) {
      $moduleActions = $this->loadRecords(
        SqlFilter::create()
          ->isEmpty('parent')
          ->andL()->comparise('module', '=', $module->module),
        array(
          array('column' => 'module'),
          array('column' => 'action_type'),
          array('column' => 'action_title')
        )
      );
      
      return $this->prepareModuleActions($moduleActions);
    }
    
    public function getModulesWithActions() {
      /* @var $moduleDao ModuleDao */
      $moduleDao = Agents::getAgent('ModuleDao', Agents::TYPE_MODEL);
      $modules = $moduleDao->loadRecords(NULL,array(array('column' => 'name')));
      $results = array();
      foreach ($modules as /* @var $module ModuleEntity */ $module) {
        $results[$module->module]['name'] = $module->name;
        $results[$module->module]['items'] = $this->getModuleActions($module);
      }
      
      return $results;
    }
  }
