<?php
  use prosys\core\common\Settings;
  
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
?><!DOCTYPE html>
<html lang="cs" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="author" content="<?php echo Settings::PROCLIENT_AUTHOR; ?>" />
    <title><?php echo (($title != Settings::WEB_NAME) ? $title . ' | ' : '') . Settings::WEB_NAME; ?></title>
    <link type="image/x-icon" rel="shortcut icon" href="<?php echo Settings::ADMIN_IMAGES; ?>favicon.ico" />
<?php include 'styles.php'; ?>
    <style type="text/css">.jqstooltip { position: absolute;left: 0px;top: 0px;visibility: hidden;background: rgb(0, 0, 0) transparent;background-color: rgba(0,0,0,0.6);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";color: white;font: 10px arial, san serif;text-align: left;white-space: nowrap;padding: 5px;border: 1px solid white;z-index: 10000;}.jqsfield { color: white;font: 10px arial, san serif;text-align: left;}</style>
    
<?php include 'javascripts.php'; ?>
  </head>
  <body<?php if (!$contentOnly) { ?>  class="page-header-fixed page-sidebar-fixed page-footer-fixed"<?php } ?>>
    <div class="header navbar navbar-inverse<?php if (!$contentOnly) { ?> navbar-fixed-top<?php } ?>"<?php if ($contentOnly) { ?> style="display: none;"<?php } ?>>
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="<?php echo Settings::ROOT_ADMIN_URL; ?>">
            <?php echo Settings::WEB_NAME_HTML; ?>
          </a>
          <a href="javascript:;" class="btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
            <img src="<?php echo Settings::ADMIN_IMAGES; ?>menu-toggler.png" alt="">
          </a>             
          <ul class="nav pull-right">
            <?php if ($_LOGGED_USER->hasRight('admin', 'send_manual')) { ?>
            <li>
              <a class="fancybox-iframe" href="http://service.styleplus.cz/admin/?module=admin&activity=send_manual"><i class="icon-envelope"></i> Poslat manuál</a>
            </li>
            <?php } ?>
            <li class="dropdown user">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-user"></i>
                <span class="username"><?php echo $_LOGGED_USER->getFullName(); ?></span>
                <i class="icon-angle-down"></i>
              </a>
              <ul class="dropdown-menu">
                <li><a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=user&activity=changeProfile"><i class="icon-user"></i> Upravit profil</a></li>
                <li class="divider"></li>
                <li><a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=user&action=logout"><i class="icon-key"></i> Odhlásit</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
	</div>
  <div class="page-container">
		<?php if (!$contentOnly) { ?>
      <div class="page-sidebar nav-collapse collapse">
        <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 528px;">
          <?php include 'menu.php'; ?>
          <div class="slimScrollBar ui-draggable" style="width: 7px; position: absolute; top: 0px; opacity: 0.3; display: none; border-top-left-radius: 7px; border-top-right-radius: 7px; border-bottom-right-radius: 7px; border-bottom-left-radius: 7px; z-index: 99; right: 1px; height: 430.222222222222px; background: rgb(161, 178, 189);"></div><div class="slimScrollRail" style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-top-left-radius: 7px; border-top-right-radius: 7px; border-bottom-right-radius: 7px; border-bottom-left-radius: 7px; opacity: 0.2; z-index: 90; right: 1px; background: rgb(51, 51, 51);"></div>
        </div>
      </div>
    <?php } ?>
		<div class="page-content"<?php if ($contentOnly) { ?> style="margin-left: 0;"<?php } ?>>
			<div class="container-fluid">
        <div id="messages_container"></div>
          <div class="content-body">