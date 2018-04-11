<?php
  use prosys\model\UserEntity,
      prosys\core\common\Settings,
      prosys\core\common\Functions;

  /* @var $user UserEntity */
  /* @var $_LOGGED_USER UserEntity */
  echo Functions::handleMessagesAdmin();
?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <h3 class="page-title">
            <?php echo $title; ?>
          </h3>
          <?php
          prosys\admin\view\View::addBreadcumb('Přehled uživatelů', 'user', '?module=user');
          prosys\admin\view\View::addBreadcumb($title, '', '?module=user&activity=manage' . (($user->isNew()) ? '' : '&id=' . $user->id));
          echo prosys\admin\view\View::getBreadcumbs();
          ?>
        </div>
      </div>
      <div class="row-fluid">
        <div class="portlet box grey">
          <div class="portlet-title">
            <div class="caption">
              <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=user"><i class="icon-user"></i>Zpět na seznam uživatelů</a>
            </div>
            <div class="tools">
              <a href="javascript:;" class="collapse"></a>
              <a href="javascript:;" class="reload"></a>
            </div>
          </div>
          <div class="portlet-body form">
                  <form action="" class="form-horizontal" method="post">
                    <input type="hidden" name="controller" value="user" />
                    <input type="hidden" name="action" value="save" />
                    <input type="hidden" name="back_to" value="<?php echo filter_input(INPUT_GET, 'activity'); ?>" />
                    <?php echo (($user->isNew()) ? '' : '<input type="hidden" name="id" value="' . $user->id . '" />'); ?>
                    <input type="hidden" name="deleted" value="0" />

                    <div class="control-group">
                      <label for="user_login" class="control-label"><?php echo $this->_labels['login']; ?>: *</label>
                      <div class="controls">
                        <?php if ($user->isNew()) { ?>
                        <input class="m-wrap medium" id="user_login" type="text" name="login" maxlength="32" value="<?php echo $user->login; ?>" placeholder="<?php echo $this->_labels['login']; ?>" autofocus />
                        <?php } else { ?>
                          <b><?php echo $user->login; ?></b>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="password" class="control-label"><?php echo $this->_labels['password']; ?>: *</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="password" type="password" name="password" placeholder="<?php echo $this->_labels['password']; ?>" />
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="repassword" class="control-label"><?php echo $this->_labels['repassword']; ?>: *</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="repassword" type="password" name="repassword" placeholder="<?php echo $this->_labels['repassword']; ?>" />
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="first_name" class="control-label"><?php echo $this->_labels['first_name']; ?>: *</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="first_name" type="text" name="first_name" maxlength="64" value="<?php echo $user->firstName; ?>" data-required="1" placeholder="<?php echo $this->_labels['first_name']; ?>"<?php echo (($user->isNew()) ? '' : ' autofocus'); ?> />
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="last_name" class="control-label"><?php echo $this->_labels['last_name']; ?>: *</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="last_name" type="text" name="last_name" maxlength="64" value="<?php echo $user->lastName; ?>" placeholder="<?php echo $this->_labels['last_name']; ?>" />
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="phone" class="control-label"><?php echo $this->_labels['phone']; ?>:</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="phone" type="text" name="phone" maxlength="64" value="<?php echo $user->phone; ?>" placeholder="<?php echo $this->_labels['phone']; ?>" />
                      </div>
                    </div>

                    <div class="control-group">
                      <label for="email" class="control-label"><?php echo $this->_labels['email']; ?>:</label>
                      <div class="controls">
                        <input class="m-wrap medium" id="email" type="text" name="email" maxlength="64" value="<?php echo $user->email; ?>" placeholder="<?php echo $this->_labels['email']; ?>" />
                      </div>
                    </div>

                    <div class="control-group">
                      <label class="control-label"><?php echo $this->_labels['userGroup']; ?>: *</label>
                      <div class="controls">
                        <?php
                          $groupName = '';
                          $groupsRes = array();
                          foreach ($groups as /* @var $group prosys\model\GroupEntity */ $group) {
                            if (!$_LOGGED_USER->hasRight('user', 'change_group')) {
                              if (in_array($group->id, $userGroups)) {
                                $groupsRes[] = '<input type="hidden" name="groups[]" value="' . $group->id . '" />' . $group->name;
                              }
                              continue;
                            }
                        ?>
                        <label class="checkbox">
                          <input type="checkbox"
                           name="groups[]" 
                           id="group_<?php echo $group->id; ?>"  
                           value="<?php echo $group->id; ?>"
                           <?php echo ((in_array($group->id, $userGroups)) ? ' checked="checked"' : ''); ?>/>
                           <?php echo $group->name; ?>
                        </label>
                        <?php
                          }
                          echo (($groupsRes) ? implode(', ', $groupsRes) : '');
                        ?>
                      </div>
                    </div>     
                    
                    <div class="mandatory">* Povinné položky</div>
                    <div class="clearfix"></div>
                    <div class="form-actions">
                      <?php if ($_LOGGED_USER->hasRight('user', 'initial')) { ?>
                        <button type="submit" class="btn green" title="Uložit a vrátit se na přehled"><i class="icon-ok"></i> Uložit</button>
                      <?php } ?>
                        <button type="submit" class="btn blue" title="Aplikovat změny" name="apply" value="1"><i class="icon-upload"></i> Použít</button>
                        <?php echo $delete; ?>
                        <a class="btn yellow cancel" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=user"><i class="icon-ban-circle"></i> Zrušit</a>
                    </div>
                  </form>
          </div>
        </div>        
      </div>
    </div>
