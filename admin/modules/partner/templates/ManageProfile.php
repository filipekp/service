<?php
  use prosys\core\common\Settings,
      prosys\core\common\Functions,
      prosys\admin\view\View;

  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  
  /* @var $partner \prosys\model\PartnerEntity */
  /* @var $user \prosys\model\UserEntity */
  $user = $partner->user;
  
  /* @var $producers \prosys\model\PartnerProducerEntity[] */

  echo Functions::handleMessagesAdmin();
?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <h3 class="page-title">
            <?php echo $title; ?>
          </h3>
          <?php
            View::addBreadcumb($title, '', '?module=partner&activity=manage_profile');
            echo View::getBreadcumbs();
          ?>
        </div>
      </div>
      
      <div class="row-fluid">
        <div class="portlet box grey">
          <div class="portlet-title">
            <div class="caption"><?php echo $this->_labels['producers']; ?></div>
          </div>

          <div class="portlet-body form">
            <form action="" class="form-horizontal" method="post">
              <input type="hidden" name="controller" value="partner" />
              <input type="hidden" name="action" value="saveProfile" />
              <input type="hidden" name="back_to" value="<?php echo filter_input(INPUT_GET, 'activity'); ?>" />

              <input type="hidden" name="deleted" value="0" />

              <input type="hidden" name="partner[name]" value="<?php echo $partner->name; ?>" />
              <input type="hidden" name="partner[hash_code]" value="<?php echo $partner->hashCode; ?>" />
              <input type="hidden" name="partner[styleplus_id]" value="<?php echo $partner->styleplusPartner->id; ?>" />

              <input type="hidden" name="id" value="<?php echo $partner->user->id ?>" />
              <input type="hidden" name="login" value="<?php echo $user->login; ?>" />
              <input type="hidden" name="password" value="" />
              <input type="hidden" name="repassword" value="" />

              <div class="row-fluid">
                <?php
                  $autofocus = TRUE;
                  foreach ($producers as $idx => $producer) {
                    $id = 'partner_producer_' . $producer->id;
                ?>
                <div class="control-group">
                  <label for="<?php echo $id; ?>" class="control-label"><?php echo $producer->producer->name; ?>:</label>
                  <div class="controls">
                    <input id="<?php echo $id; ?>" class="m-wrap small" type="text" name="partner_producer[<?php echo $producer->id; ?>]" value="<?php echo $producer->profit; ?>" placeholder="<?php echo mb_strtolower($this->_labels['profit']); ?>" <?php echo (($autofocus) ? 'autofocus' : ''); ?> /> <span class="text">%</span>
                  </div>
                </div>
                <?php
                    $autofocus = FALSE;
                  }
                ?>
              </div>

              <div class="mandatory">* Povinné položky</div>
              <div class="clearfix"></div>
              <div class="form-actions">
                  <button type="submit" class="btn blue" title="Aplikovat změny" name="apply" value="1"><i class="icon-upload"></i> Uložit</button>
                  <a class="btn yellow cancel nomargin" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner&activity=<?php echo filter_input(INPUT_GET, 'activity'); ?>"><i class="icon-ban-circle"></i> Zrušit</a>
              </div>
            </form>
          </div>
        </div>        
      </div>
    </div>
