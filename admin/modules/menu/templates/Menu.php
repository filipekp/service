<?php
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  function generateMenu($menus, $level = 1) {
    /* @var $_LOGGED_USER UserEntity */
    global $_MODULE, $activity, $_LOGGED_USER;
    
    /* @var $moduleActionDao \prosys\model\ModuleActionDao */
    $moduleActionDao = Agents::getAgent('ModuleActionDao', Agents::TYPE_MODEL);
    
    $classActive = 'active';
    
    ob_start();
    foreach ($menus as /* @var $menu prosys\model\MenuEntity */ $menu) {
      $classes = array();
      $aClasses = array();
      $classes[] = 'level'.$level;
      $aditionalAttributes = array();
      $render = TRUE;
      switch ($menu->type) {
        case 'module_action':
          /* @var $moduleAction \prosys\model\ModuleActionEntity */
          $moduleAction = $moduleActionDao->load($menu->typeValue);
          $active = $moduleAction->module->module == $_MODULE && $moduleAction->name == $activity;
          $dataLink = (($moduleAction->type == 1) ? '?module=' . $moduleAction->module->module . (($moduleAction->name == 'initial') ? '' : '&activity=' . $moduleAction->name) :
                  (($moduleAction->type == 2) ? '?controller=' . $moduleAction->module->module . '&action=' . $moduleAction->name : ''));
          $link = $dataLink;
          if (!$_LOGGED_USER->hasRight($moduleAction->module->module, $moduleAction->name)) { $render = FALSE; }
        break;
        case 'link':
          $data = json_decode($menu->typeValue, TRUE);
          $link = $data['href'];
          $dataLink = '';
          $active = FALSE;
          unset($data['href']);
          foreach ($data as $attributte => $value) {
            if ($attributte == 'class') {
              $aClasses[] = $value;
            } else {
              $aditionalAttributes[] = $attributte . '="' . $value . '"';
            }
          }
        break;
        case 'section':
          $active = FALSE;
          $data = (array)json_decode($menu->typeValue, TRUE);
          foreach ($data as $attributte => $value) {
            if ($attributte == 'class') {
              $aClasses[] = $value;
            } else {
              $aditionalAttributes[] = $attributte . '="' . $value . '"';
            }
          }
          $link = '#';
          $dataLink = '';
        break;
      }
      
      $icons = (array)json_decode($menu->icons);
      if ($icons) {
        array_walk($icons, function(&$icon) {
          $icon = '<i class="icon-' . $icon . '"></i>';
        });
      }
      
      $sub_menu = '';
      
      if ($menu->children) {
        $submenuHtml = trim(generateMenu($menu->children, ($level + 1)));
        if ($submenuHtml) {
          $sub_menu = '<ul class="sub-menu">' . $submenuHtml . '</ul>';
        } elseif (!$menu->displayAlways) {
          $render = FALSE;
        }
      }
      
      if (($active)) { $classes[] = $classActive; }
      
      if ($render) {
        $href = ((preg_match('@^(/)|(https?://)|(#).*@', $link)) ? '' : Settings::ROOT_ADMIN_URL) . $link;
    ?>
  <li<?php echo (($classes) ? ' class="' . implode(' ', $classes) . '"' : ''); ?> data-id="<?php echo $menu->id; ?>" data-parent="<?php echo (($menu->parent) ? $menu->parent->id : '0'); ?>">
    <a data-link="<?php echo $dataLink; ?>" href="<?php echo $href; ?>"<?php echo (($aClasses) ? ' class="' . implode(' ', $aClasses) . '"' : ''); ?><?php echo (($aditionalAttributes) ? ' ' . implode(' ', $aditionalAttributes) : ''); ?>>
      <?php echo (($icons) ? '<span class="icons">' . implode('', $icons) . '</span>' : ''); ?> <span class="title"><?php echo $menu->name; ?></span><?php echo (($sub_menu) ? ' <span class="arrow"></span>' : ''); ?>
    </a>
    <?php echo $sub_menu; ?>
  </li>
<?php
      }
    }
    
    return ob_get_clean();
  }
?>
<ul class="page-sidebar-menu" style="overflow: hidden; width: auto; height: 528px;">
  <li>
    <div class="sidebar-toggler hidden-phone"></div>
  </li>
  <li>&nbsp;</li>
  <?php echo generateMenu($menus); ?>
</ul>