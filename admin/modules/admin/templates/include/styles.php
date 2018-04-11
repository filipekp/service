<?php 
  use prosys\admin\view\View,
      prosys\core\common\Settings;
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  global $_LOGGED_USER;
  
  $plugins = Settings::ADMIN_JS_PLUGINS; 
  $css = Settings::ADMIN_CSS;
  
  echo View::htmlIncludeCSS('jquery-metro.css', $plugins . 'jquery-ui/');
  echo View::htmlIncludeCSS('bootstrap.css', $plugins . 'bootstrap/css/');
  echo View::htmlIncludeCSS('bootstrap-responsive.css', $plugins . 'bootstrap/css/');
  echo View::htmlIncludeCSS('font-awesome.min.css', $plugins . 'font-awesome/css/');
  echo View::htmlIncludeCSS('style-metro-my.css');
  echo View::htmlIncludeCSS('style.css');
  echo View::htmlIncludeCSS('style-responsive.css');
  echo View::htmlIncludeCSS($_LOGGED_USER->theme . '.css', $css . 'themes/');
  echo View::htmlIncludeCSS('profile.css', $css . 'pages/');
  echo View::htmlIncludeCSS('error.css', $css . 'pages/');
  echo View::htmlIncludeCSS('uniform.default.css', $plugins . 'uniform/css/');
  
  echo View::htmlIncludeCSS('jquery.gritter.css', $plugins . 'gritter/css/');
  echo View::htmlIncludeCSS('daterangepicker.css', $plugins . 'bootstrap-daterangepicker/');
  echo View::htmlIncludeCSS('datetimepicker.css', $plugins . 'bootstrap-datetimepicker/css/');
  echo View::htmlIncludeCSS('select2.css', $plugins . 'select2/');
  echo View::htmlIncludeCSS('jqvmap.css', $plugins . 'jqvmap/jqvmap/');
  echo View::htmlIncludeCSS('jquery.easy-pie-chart.css', $plugins . 'jquery-easy-pie-chart/');
  echo View::htmlIncludeCSS('glyphicons.css', $plugins . 'glyphicons/css/');
  echo View::htmlIncludeCSS('halflings.css', $plugins . 'glyphicons_halflings/css/');
  echo View::htmlIncludeCSS('bootstrap-fileupload.css', $plugins . 'bootstrap-fileupload/');
  echo View::htmlIncludeCSS('bootstrap-toggle-buttons.css', $plugins . 'bootstrap-toggle-buttons/static/stylesheets/');
  echo View::htmlIncludeCSS('bootstrap-switch.css', $plugins . 'bootstrap-switch/css/bootstrap3/');
  echo View::htmlIncludeCSS('jquery.fancybox.css', $plugins . 'fancybox/source/');
  echo View::htmlIncludeCSS('chosen.css', $plugins . 'chosen-bootstrap/chosen/');
  echo View::htmlIncludeCSS('bootstrap-modal.css', $plugins . 'bootstrap-modal/css/');
  echo View::htmlIncludeCSS('modules.css');
  
  $this->includeModuleCSS();