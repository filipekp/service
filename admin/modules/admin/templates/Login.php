<?php
use prosys\core\common,
    prosys\core\common\Functions,
    prosys\core\common\Settings; 
?><!DOCTYPE html>

<html lang="cs" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <title>Přihlášení - <?php echo common\Settings::WEB_NAME; ?></title>

    <meta name="author" content="<?php echo common\Settings::PROCLIENT_AUTHOR; ?>" />
    
    <?php
      include 'include/stylesLogin.php';
      include 'include/javascripts.php';
    ?>
  </head>
	<body class="login">
    <div class="logo">
      <?php echo Settings::WEB_NAME_HTML; ?>
    </div>
    
    <div class="content">
      <form class="form-vertical login-form" action="index.php" method="POST">
        <input type="hidden" name="controller" value="user" />
        <input type="hidden" name="action" value="login" />
        
        <h3 class="form-title">Přihlášení</h3>
        <div id="messages_container"></div>
        <?php echo Functions::handleMessagesAdmin(); ?>
        
        <div class="control-group">
          <label class="control-label visible-ie8 visible-ie9">Uživatelské jméno</label>
          <div class="controls">
            <div class="input-icon left">
              <i class="icon-user"></i>
              <input class="m-wrap placeholder-no-fix" type="text" placeholder="Uživatelské jméno" name="login"/>
            </div>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label visible-ie8 visible-ie9">Heslo</label>
          <div class="controls">
            <div class="input-icon left">
              <i class="icon-lock"></i>
              <input class="m-wrap placeholder-no-fix" type="password" placeholder="Heslo" name="password"/>
            </div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn green pull-right">
          Přihlásit <i class="m-icon-swapright m-icon-white"></i>
          </button>            
        </div>
      </form>
      <!-- END LOGIN FORM -->
    </div>
	</body>
  <script>
		jQuery(document).ready(function() {     
		  App.init();
		  Login.init();
		});
	</script>
</html>
