<?php 
  use prosys\core\common\Settings,
      prosys\core\common\Functions; ?>

<?php echo Functions::handleMessagesAdmin(); ?>

<div class="container-fluid">
  <div class="row-fluid">
    <div class="span12">
      <h3 class="page-title">
        <?php echo $title; ?> <small>(celkem záznamů: <?php echo $count; ?>)</small>
      </h3>
      <?php
      prosys\admin\view\View::addBreadcumb($title, '', '?module=group&activity=manage');
      echo prosys\admin\view\View::getBreadcumbs();
      ?>
    </div>
  </div>

  <div class="row-fluid">
    <div class="span12">
      <div class="portlet box grey">
        <div class="portlet-title">
          <div class="caption">
            <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group&activity=manage"><i class="icon-plus"></i>Přidat skupinu</a>
          </div>
          <div class="tools">
            <a href="javascript:;" class="collapse"></a>
            <a href="javascript:;" class="reload"></a>
          </div>
        </div>
        <div class="portlet-body no-more-tables">
          <form class="filter" method="get">
            <table class="table-bordered table-striped table-condensed cf manage-table">
              <thead class="cf">
                <tr>
                  <th class="column_1"><?php echo $this->_labels['id']; ?></th>
                  <th class="column_2"><?php echo $this->_labels['name']; ?></th>
                  <th class="icon"></th>
                  <th class="icon"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                  /* @var $group prosys\model\GroupEntity */
                  foreach ($data as $group) {
                ?>
                <tr id="object_id_<?php echo $group->id; ?>">
                  <td class="column_1" data-title="<?php echo $this->_labels['id']; ?>"><?php echo $group->id; ?></td>
                  <td class="column_2" data-title="<?php echo $this->_labels['name']; ?>"><?php echo $group->name; ?></td>
                  <td class="icon">
                    <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group&activity=manage&id=<?php echo $group->id; ?>" class="icon_link default_action">
                      <i class="icon-pencil"></i>
                    </a>
                  </td>
                  <td class="icon">
                    <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=group&action=delete&id=<?php echo $group->id; ?>" class="icon_link" onclick="return confirm('Opravdu chcete skupinu `<?php echo $group->name; ?>` smazat?');">
                      <i class="icon-trash"></i>
                    </a>
                  </td>
                </tr>
                <?php
                  }
                  if (!$data) {
                ?>
                <tr class="no_object">
                  <td colspan="4"><?php echo (($filter) ? 'Nenalezen' : 'Není'); ?> žádná skupina</td>
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
