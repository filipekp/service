<?php 
  use prosys\admin\view\View,
      prosys\core\common\Settings;
  
  $plugins = Settings::ADMIN_JS_PLUGINS; 
  $css = Settings::ADMIN_CSS;
  
  echo View::htmlIncludeCSS('bootstrap.css', $plugins . 'bootstrap/css/');
  echo View::htmlIncludeCSS('bootstrap-responsive.css', $plugins . 'bootstrap/css/');
  echo View::htmlIncludeCSS('font-awesome.min.css', $plugins . 'font-awesome/css/');
  echo View::htmlIncludeCSS('style-metro.css');
  echo View::htmlIncludeCSS('style.css');
  echo View::htmlIncludeCSS('style-responsive.css');
  echo View::htmlIncludeCSS('default.css', $css . 'themes/');
  echo View::htmlIncludeCSS('uniform.default.css', $plugins . 'uniform/css/');
  echo View::htmlIncludeCSS('login.css', $css . 'pages/');
  
  $this->includeModuleCSS();
  