<?php use prosys\core\common\Settings; ?>
  <div class="navigation">
    <a class="add_user" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group&activity=manage">Přidat skupinu</a>
    <div class="clear_both"></div>
  </div>

<div id="content_inner">
  <?php echo prosys\core\common\Functions::handleMessagesAdmin(); ?>
  <h1><?php echo $title; ?></h1>
  <div id="list">
    <table id="groups">
      <thead>
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
          <td class="column_1"><?php echo $group->id; ?></td>
          <td class="column_2"><?php echo $group->name; ?></td>
          <td class="icon">
            <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=group&activity=manage&id=<?php echo $group->id; ?>" class="icon_link">
              <img src="<?php echo Settings::ADMIN_MODULES; ?>user/images/user-edit.png" alt="Upravit" title="Upravit" />
            </a>
          </td>
          <td class="icon">
            <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=group&action=delete&id=<?php echo $group->id; ?>" class="icon_link" onclick="return confirm('Opravdu chcete uživatele smazat?');">
              <img src="<?php echo Settings::ADMIN_MODULES; ?>user/images/user-delete.png" alt="Smazat" title="Smazat" />
            </a>
          </td>
        </tr>
        <?php
          }
          if (!$data) {
        ?>
        <tr class="no_object">
          <td colspan="4">Nejsou žádná data</td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
  