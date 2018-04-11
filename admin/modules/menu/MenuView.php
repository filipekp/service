<?php
  namespace prosys\admin\view;
  use prosys\core\common\Agents;

  /**
   * Represents the admin menu module view.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class MenuView extends View
  {
    /**
     * Initializes the label of every group property.
     */
    public function __construct() {
      $labels = array(
        'id'    => 'ID',
        'name'  => 'Jméno skupiny'
      );

      parent::__construct(NULL, $labels);
    }
    
    public function getMenu() {
      global $_MODULE;
      
      $module = strtolower($_MODULE);
      $activityMenu = filter_input(INPUT_GET, 'activity');
      
      /* @var $menuDao \prosys\model\MenuDao */
      $menuDao = Agents::getAgent('MenuDao', Agents::TYPE_MODEL);
      $menus = $menuDao->loadMenuRecursive();
      
      $assign = array('menus' => $menus, 'module' => $module, 'activity' => $activityMenu);
      
      $heading = '';
      
      $templateOnly = TRUE;
      ob_start();
        $this->printActivity('Menu', $heading, $assign, $templateOnly);
      $html = ob_get_clean();

      return $html;
    }
  }
