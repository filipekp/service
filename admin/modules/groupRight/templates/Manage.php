<?php
  use prosys\core\common\Settings;

  echo \prosys\core\common\Functions::handleMessagesAdmin();
  /* @var $group \prosys\model\GroupEntity */  
?>
<div class="navigation">
  <a class="groups" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group">Zpět na seznam skupin</a>
  <div class="clear_both"></div>
</div>

<div id="content_inner">
  <h1><?php echo $title; ?></h1>
  <div id="manage_form">
    <form id="manage" method="post">
      <input type="hidden" name="controller" value="group" />
      <input type="hidden" name="action" value="save" />
      <?php echo (($group->isNew()) ? '' : '<input type="hidden" name="id" value="' . $group->id . '" />'); ?>
      
      <table id="manage_user_table">
        <tbody>
          <tr>
            <th><label for="name"><?php echo $this->_labels['name']; ?>: *</label></th>
            <td>
              <input class="text" id="name" type="text" name="name" value="<?php echo $group->name; ?>" autofocus />
            </td>
          </tr>
          <tr class="empty"><td colspan="2">&nbsp;</td></tr>

          <tr class="save">
            <th>&nbsp;</th>
            <td><input id="save" class="submit" type="submit" value="Uložit" title="Uložit a vrátit se na přehled" /></td>
          </tr>
          <tr class="save">
            <th>&nbsp;</th>
            <td><input id="save" class="submit" type="submit" name="apply" value="Použít" title="Aplikovat změny" /></td>
          </tr><?php echo $delete; ?>
        </tbody>
      </table>
    </form>

    <div class="mandatory">* Povinné položky</div>
  </div>
</div>
