<?php
  namespace prosys\model;
  use prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;
  /**
   * Represents the menu data access object.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class MenuDao extends MyDataAccessObject
  {
    public function __construct() {
      parent::__construct('menu_items', MenuEntity::classname());
    }
    
    public function loadMenuRecursive($parentId = 0) {
      $filter = SqlFilter::create();
      if ($parentId) {
        $filter->comparise('parent', '=', $parentId);
      } else {
        $filter->isEmpty('parent');
      }
      
      $filter->andL()->comparise('display_always', '>=', '0');
      
      $menus = $this->loadRecords($filter, array(array('column' => 'parent', 'direction' => 'asc'), array('column' => 'sequence', 'direction' => 'asc')));
      
      foreach ($menus as &$menu) {
        /* @var $menu MenuEntity */
        $children = $this->loadMenuRecursive($menu->id);
        if ($children) {
          $menu->children = $children;
        }
      }

      return $menus;
    }
    
    /**
     * Vrátí menu entity podle zadaného modulu pro aktivitu initial.
     * 
     * @param string $module
     * @param string $activity
     * 
     * @return MenuEntity
     */
    public function loadByModule($module, $activity = 'initial') {
      return Functions::first($this->loadRecords(
        SqlFilter::create()
          ->inFilter('type_value',
            SqlFilter::create()
              ->filter('id', 'module_actions', 
                SqlFilter::create()
                  ->comparise('module', '=', $module)
                  ->andL()->comparise('action_name', '=', $activity)))
      ));
    }
  }