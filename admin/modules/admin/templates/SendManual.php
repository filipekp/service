<?php
  use prosys\core\common\Functions;
  
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  global $_LOGGED_USER;

  echo Functions::handleMessagesAdmin();
?>

      <div class="row-fluid">
        <div class="portlet box grey">
          <div class="portlet-title">
            <div class="caption"><?= $title; ?></div>
          </div>

          <div class="portlet-body form">
            <form action="" class="horizontal-form filter" method="post">
              <input type="hidden" name="controller" value="admin" />
              <input type="hidden" name="action" value="sendManual" />
              <input type="hidden" name="attached_manual" value="<?= ((isset($attachedManual)) ? (bool)$attachedManual : 0); ?>" />

              <div class="control-group">
                <label for="email" class="control-label"><?= $this->_labels['email']['address']; ?>:</label>
                <div class="controls">
                  <input class="m-wrap span12" id="email" type="text" name="email" value="<?= $recepientMail; ?>" placeholder="<?= $this->_labels['email']['email']; ?>" autofocus="autofocus" />
                </div>
              </div>

              <div class="control-group">
                <label for="subject" class="control-label"><?= $this->_labels['email']['subject']; ?>:</label>
                <div class="controls">
                  <input class="m-wrap span12" id="subject" type="text" name="subject" placeholder="<?= $this->_labels['email']['subject']; ?>" value="<?= $defaultSubject; ?>" />
                </div>
              </div>

              <div class="control-group">
                <label for="body" class="control-label"><?= $this->_labels['email']['body']; ?>:</label>
                <div class="controls">
                  <textarea id="body" class="tinymce_mini" name="body" placeholder="<?= $this->_labels['email']['body']; ?>" style="height: 200px;"><?= $defaultBody; ?></textarea>
                </div>
              </div>
              
              <div class="control-group tright">
                <label class="control-label">&nbsp;</label>
                <?php if ($_LOGGED_USER->hasRight('admin', 'sendManual')) { ?>
                  <button type="submit" class="btn green" title="Odeslat manuál">Odeslat manuál&nbsp;&nbsp;<i class="icon-envelope"></i></button>
                <?php } ?>
              </div>
            </form>
          </div>
        </div>
      </div>
