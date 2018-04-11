<?php 
  use prosys\core\common\Settings,
      prosys\core\common\Functions;
  
  /* @var $_LOGGED_USER \prosys\model\UserEntity */

  echo Functions::handleMessagesAdmin();
?>

  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <h3 class="page-title">
          <?php echo $title; ?> <small>(celkem záznamů: <?php echo $count; ?>)</small>
        </h3>
        <?php
        prosys\admin\view\View::addBreadcumb($title, '', '?module=user&activity=manage');
        echo prosys\admin\view\View::getBreadcumbs();
        ?>
      </div>
    </div>
    <div class="row-fluid">
      <div class="span12">
        <div class="portlet box grey">
          <div class="portlet-title">
            <div class="caption">
              <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=user&activity=manage"><i class="icon-plus"></i>Přidat uživatele</a>
            </div>
            <div class="tools">
              <a href="javascript:;" class="collapse"></a>
              <a href="javascript:;" class="reload"></a>
            </div>
          </div>
          <div class="portlet-body no-more-tables">
            <form class="filter" method="get">
              <input type="hidden" name="module" value="user" />
              <table class="table-bordered table-striped table-condensed cf manage-table">
                <thead class="cf">
                  <tr>
                    <th class="column_1"><?php echo $this->_labels['login']; ?></th>
                    <th class="column_2"><?php echo $this->_labels['first_name']; ?></th>
                    <th class="column_3"><?php echo $this->_labels['contact']; ?></th>
                    <th class="icon"></th>
                    <th class="icon"></th>
                  </tr>
                  <tr>
                    <td class="column_1">
                      <input type="text" class="text filter_input" name="filter_login" value="<?php echo $filter['filter_login']; ?>"/>
                    </td>
                    <td class="column_2">
                      <input type="text" class="text filter_input" name="filter_name" value="<?php echo $filter['filter_name']; ?>"/>
                    </td>
                    <td class="column_3"></td>
                    <td class="icon"></td>
                    <td class="icon">
                      <input type="submit" class="halflings-icon filter filter-icon" value="" title="Aplikovat filtr" />
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($data) {
                    /* @var $user prosys\model\UserEntity */
                    foreach ($data as $user) {
                  ?>
                  <tr id="object_id_<?php echo $user->login; ?>">
                    <td class="column_1" data-title="<?php echo $this->_labels['login']; ?>"><?php echo $user->login; ?></td>
                    <td class="column_2" data-title="<?php echo $this->_labels['first_name']; ?>"><?php echo $user->getFullName(); ?></td>
                    <td class="column_3" data-title="<?php echo $this->_labels['contact']; ?>"><?php echo $user->getContact(); ?></td>
                    <?php if ($_LOGGED_USER->hasRight('user', 'manage')) { ?>
                    <td class="icon" data-title="Editovat">
                      <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=user&activity=manage&id=<?php echo $user->id; ?>" class="icon_link default_action">
                        <i class="icon-pencil"></i>
                      </a>
                    </td>
                    <?php
                          } else {
                    ?>
                      <td class="icon">&nbsp;</td>
                    <?php } ?>
                    <?php if ($user->login != $_LOGGED_USER->login && $_LOGGED_USER->hasRight('user', 'delete')) { ?>
                    <td class="icon" data-title="Smazat">
                      <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=user&action=delete&id=<?php echo $user->id; ?>" class="icon_link" onclick="return confirm('Opravdu chcete uživatele smazat?');">
                        <i class="icon-trash"></i>
                      </a>
                    </td>
                  <?php
                        } else {
                  ?>
                    <td class="icon">&nbsp;</td>
                  <?php } ?>
                  </tr>
                  <?php
                    }
                  } else {
                  ?>  
                  <tr class="no_object">                  
                    <td colspan="5"><?php echo (($filter) ? 'Nenalezen' : 'Není'); ?> žádný uživatel</td>
                  </tr>
                  <?php
                  }
                  ?>
                </tbody>
              </table>
            </form>
            
            <?php echo (($count > Settings::ITEMS_PER_PAGE) ? '<br />' . $pagination : ''); ?>
          </div>
        </div>
      </div>
    </div>
  </div>