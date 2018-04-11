<?php
  use prosys\core\common\Settings,
      prosys\model\ModuleActionEntity,
      prosys\core\common\Functions;

  echo Functions::handleMessagesAdmin();
  /* @var $group \prosys\model\GroupEntity */  
?>

<div class="container-fluid">
  <div class="row-fluid">
    <div class="span12">
      <h3 class="page-title">
        <?php echo $title; ?>
      </h3>
      <?php
      prosys\admin\view\View::addBreadcumb('Přehled skupin', 'group', '?module=group');
      prosys\admin\view\View::addBreadcumb($title, '', '?module=group&activity=manage' . (($group->isNew()) ? '' : '&id=' . $group->id));
      echo prosys\admin\view\View::getBreadcumbs();
      ?>
    </div>
  </div>
  <div class="row-fluid">
    <div class="portlet box grey">
      <div class="portlet-title">
        <div class="caption">
          <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group"><i class="icon-group"></i>Zpět na seznam skupin</a>
        </div>
        <div class="tools">
          <a href="javascript:;" class="collapse"></a>
          <a href="javascript:;" class="reload"></a>
        </div>
      </div>
      <div class="portlet-body form rights">
        <form action="" class="form-horizontal" method="post">
          <input type="hidden" name="controller" value="group" />
          <input type="hidden" name="action" value="save" />
          <?php echo (($group->isNew()) ? '' : '<input type="hidden" name="id" value="' . $group->id . '" />'); ?>

          <div class="control-group">
            <label for="name" class="control-label"><?php echo $this->_labels['name']; ?>: *</label>
            <div class="controls">
              <input class="m-wrap medium" id="name" type="text" name="name" value="<?php echo $group->name; ?>" placeholder="<?php echo $this->_labels['name']; ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label"><?php echo $this->_labels['rights']; ?>: *</label>
            <div class="controls">
              <?php echo \prosys\admin\view\GroupView::getHtmlGroupRights(array('modulesWithAction' => $modulesWithAction, 'groupRights' => $groupRights)); ?>
            </div>
          </div>
          
          <div class="mandatory">* Povinné položky</div>
          <div class="clearfix"></div>

          <div class="form-actions">
            <?php if ($_LOGGED_USER->hasRight('group', 'initial')) { ?>
              <button type="submit" class="btn green" title="Uložit a vrátit se na přehled"><i class="icon-ok"></i> Uložit</button>
            <?php } ?>
              <button type="submit" class="btn blue" title="Aplikovat změny" name="apply" value="1"><i class="icon-upload"></i> Použít</button>
              <?php echo $delete; ?>
          </div>
        </form>
      </div>
    </div>        
  </div>
</div>
