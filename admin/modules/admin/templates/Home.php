<?php 
  use prosys\core\common\Functions;
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  global $_LOGGED_USER;
  
  echo Functions::handleMessagesAdmin();
?>

<div id="content_inner">
  <h1>Hlavní strana</h1>
  <div id="main">
    <div class="home_page_info">
      Uživatel <strong><?php echo $_LOGGED_USER->getFullName(); ?></strong> přihlášen<br />
      Vaše IP adresa: <strong><?php echo filter_input(INPUT_SERVER, 'REMOTE_ADDR'); ?></strong><br />
      <br />
      <b>Datum a čas</b><br />
      Právě je: <strong><?php echo date('d.m.Y H:i:s'); ?></strong>
    </div>
  </div>
</div>
