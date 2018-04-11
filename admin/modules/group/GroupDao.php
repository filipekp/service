<?php
  namespace prosys\model;
  
  use prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter;

  /**
   * Represents the group data access object.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class GroupDao extends MyDataAccessObject
  {
    /** @var GroupRightDao */
    private $_groupRightDao;
    
    public function __construct()
    {
      parent::__construct('groups', GroupEntity::classname());
      
      $this->_groupRightDao = Agents::getAgent('GroupRightDao', Agents::TYPE_MODEL);
    }
    
    /**
     * Smaze skupinu vc. prav.
     * 
     * @param mixed $arg
     * @return bool
     */
    public function delete($arg) {
      $arg = ((is_object($arg)) ? $arg : $this->load($arg));
      $this->_groupRightDao->deleteRecords(
        SqlFilter::create()->comparise('group_id', '=', $arg->id)
      );
      
      return parent::delete($arg);
    }
    
    /**
     * Uloží prava skupiny.
     * 
     * @param GroupEntity $group
     * @param array $rights
     * 
     * @return bool
     */
    public function assignGroupGroupRights(GroupEntity $group, array $rights) {
      // jako hodnoty prav da id akce modulu
      $current = array_map(function($right) {
        return $right->action->id;
      }, $group->rights->getLoadedArrayCopy());

      // spolecne prvky stavajicich a novych prav
      $intersect = array_intersect($current, $rights);
      
      // smazu
      if (($toDelete = array_diff($current, $intersect))) {
        $this->_groupRightDao->deleteRecords(
          SqlFilter::create()->comparise('group_id', '=', $group->id)
                             ->andL()
                             ->inArray('module_action', $toDelete)
        );
      }
      
      // pridam
      array_map(function($right) use ($group) {
        $this->_groupRightDao->store($this->_groupRightDao->load(
          array(
            'group_id'       => $group,
            'module_action'  => $right,
            'is_uncheckable' => TRUE
          ))
        );
      }, array_diff($rights, $intersect));
    }
  }
