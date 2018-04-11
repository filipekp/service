<?php 
  use prosys\core\common\Agents;
  
  /* @var $menuView \prosys\admin\view\MenuView */
  $menuView = Agents::getAgent('MenuView', Agents::TYPE_VIEW_ADMIN);
  
  $classActive = 'active';
  $active = ' class="' . $classActive . '"';
  
  $module = strtolower($_MODULE);
  $activityMenu = filter_input(INPUT_GET, 'activity');
  
  echo $menuView->getMenu();
