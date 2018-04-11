<?php 
  use prosys\admin\view\View;
  
  $plugins = prosys\core\common\Settings::ADMIN_JS_PLUGINS;

  
  echo View::htmlIncludeJS('jquery-1.10.1.min.js', $plugins);
  echo View::htmlIncludeJS('jquery-migrate-1.2.1.min.js', $plugins);
  //echo View::htmlIncludeJS('jquery-ui-1.10.1.custom.min.js', $plugins . 'jquery-ui/');
  echo View::htmlIncludeJS('jquery-ui.js');
  echo View::htmlIncludeJS('bootstrap.min.js', $plugins . 'bootstrap/js/');
  ?><!--[if lt IE 9]><?php
	echo View::htmlIncludeJS('excanvas.min.js', $plugins);
  echo View::htmlIncludeJS('respond.min.js', $plugins); 
	?><![endif]--><?php
  echo View::htmlIncludeJS('jquery.slimscroll.min.js', $plugins . 'jquery-slimscroll/');
  echo View::htmlIncludeJS('chosen.jquery.min.js', $plugins . 'chosen-bootstrap/chosen/');
  echo View::htmlIncludeJS('select2.js', $plugins . 'select2/');
  echo View::htmlIncludeJS('jquery.blockui.min.js', $plugins);
  echo View::htmlIncludeJS('jquery.cookie.min.js', $plugins);
  echo View::htmlIncludeJS('jquery.uniform.js', $plugins . 'uniform/');  
  echo View::htmlIncludeJS('jquery.flot.js', $plugins . 'flot/');
  echo View::htmlIncludeJS('jquery.flot.resize.js', $plugins . 'flot/');
  echo View::htmlIncludeJS('jquery.pulsate.min.js', $plugins);
  echo View::htmlIncludeJS('date.js', $plugins . 'bootstrap-daterangepicker/');
  echo View::htmlIncludeJS('daterangepicker.js', $plugins . 'bootstrap-daterangepicker/');
  echo View::htmlIncludeJS('jquery.gritter.js', $plugins . 'gritter/js/');
  echo View::htmlIncludeJS('jquery.validate.min.js', $plugins . 'jquery-validation/dist/');
  echo View::htmlIncludeJS('bootstrap-fileupload.js', $plugins . 'bootstrap-fileupload/');
  echo View::htmlIncludeJS('jquery.toggle.buttons.js', $plugins . 'bootstrap-toggle-buttons/static/js/');
  echo View::htmlIncludeJS('jquery-ui.js', $plugins . 'jquery-ui/');
//  echo View::htmlIncludeJS('bootstrap-timepicker.js', $plugins . 'bootstrap-timepicker/js/');
//  echo View::htmlIncludeJS('bootstrap-datepicker.js', $plugins . 'bootstrap-datepicker/js/');
//  echo View::htmlIncludeJS('bootstrap-datetimepicker.js', $plugins . 'bootstrap-datetimepicker/js/');
//  echo View::htmlIncludeJS('bootstrap-datetimepicker.cs.js', $plugins . 'bootstrap-datetimepicker/js/locales/');
  echo View::htmlIncludeJS('bootstrap-modal.js', $plugins . 'bootstrap-modal/js/');
  echo View::htmlIncludeJS('bootstrap-modalmanager.js', $plugins . 'bootstrap-modal/js/');
  echo View::htmlIncludeJS('bootstrap-switch.js', $plugins . 'bootstrap-switch/js/');
  echo View::htmlIncludeJS('jquery.fancybox.js', $plugins . 'fancybox/');
  echo View::htmlIncludeJS('jquery.inputmask.js', $plugins . 'inputmask-multi/');
  echo View::htmlIncludeJS('jquery.bind-first.js', $plugins . 'inputmask-multi/');
  echo View::htmlIncludeJS('jquery.inputmask-multi.js', $plugins . 'inputmask-multi/');
  echo View::htmlIncludeJS('jquery.inputmask.extensions.js', $plugins . 'inputmask-multi/');
  echo View::htmlIncludeJS('jquery.inputmask.phone.extensions.js', $plugins . 'inputmask-multi/');
  echo View::htmlIncludeJS('form-components.js');
  echo View::htmlIncludeJS('app.js');
  echo View::htmlIncludeJS('login.js');
  echo View::htmlIncludeJS('index.js');
  echo View::htmlIncludeJS('ui-modals.js');
  
  echo View::htmlIncludeJS('tinymce.min.js', $plugins . 'tinymce/');
  echo View::htmlIncludeJS('custom.js');
  ?>
  <script>
    location.current_path = '<?php echo \prosys\core\common\Settings::ROOT_ADMIN_URL; ?>';
  </script>
  <?php
  
  $this->includeModuleJS(); 

