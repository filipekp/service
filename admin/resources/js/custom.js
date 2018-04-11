/**
 * Logs object into the console with specific label.
 * 
 * @param {object} object
 * @param {string} label
 * @param {object} style default style is green background with white font color → object is css object in the correct form
 */
var debug = {
  log: function(object, label, style) {
    var defaultStyle = { 'background-color': '#007F04', 'color': 'white' };
    style = decodeURIComponent($.param($.extend(defaultStyle, ((typeof style === 'undefined') ? {} : style))))
              .replace(/=/g, ': ').replace(/&/g, '; ');

    var objSpecifier;
    switch (typeof object) {
      case 'string':
      case 'undefined':
        objSpecifier = '%s';
      break;
      
      case 'boolean':
        objSpecifier = '%s';
        object = ((object) ? true : false);
      break;
      
      case 'number':
        objSpecifier = ((parseInt(object) !== parseFloat(object)) ? '%f' : '%d');
      break;

      case 'object':
      case 'function':
      case 'xml':
      default:
        objSpecifier = '%O';
      break;
    }
    
    console.log('%c ' + ((typeof label === 'undefined') ? 'debug' : label) + ': %c  ' + objSpecifier, style, 'background-color: white;', object);
  }
};

/**
 * Repeats a string.
 * 
 * @param {Integer} num
 * @returns {String}
 */
String.prototype.repeat = function(num) {
  return new Array(num + 1).join(this);
};

/**
 * Get TRUE when key in array exists.
 * 
 * @param {mixed} key
 * @param {array|object} search
 * @returns {Boolean}
 */
function array_key_exists(key, search) {
  if (!search || (search.constructor !== Array && search.constructor !== Object)) {
    return false;
  }

  return key in search;
}

function round(value, precision, mode) {
  var m, f, isHalf, sgn;
  precision |= 0;
  m = Math.pow(10, precision);
  value *= m;
  sgn = (value > 0) | -(value < 0);
  isHalf = value % 1 === 0.5 * sgn;
  f = Math.floor(value);

  if (isHalf) {
    switch (mode) {
      case 'PHP_ROUND_HALF_DOWN':
        value = f + (sgn < 0); // rounds .5 toward zero
        break;
      case 'PHP_ROUND_HALF_EVEN':
        value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
        break;
      case 'PHP_ROUND_HALF_ODD':
        value = f + !(f % 2); // rounds .5 towards the next odd integer
        break;
      default:
        value = f + (sgn > 0); // rounds .5 away from zero
    }
  }

  return (isHalf ? value : Math.round(value)) / m;
}

/**
 * Set the cookie.
 * 
 * @param {string} cname
 * @param {mixed} cvalue
 * @param {int} exdays
 */
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+d.toGMTString();
  document.cookie = cname + '=' + cvalue + '; ' + expires + '; path=/;';
}

/**
 * Get the cookie.
 * @param {string} cname
 */
function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1);
    if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
  }
  return "";
}

/***** STATUS MESSAGES *****/
var TIMER_FOR_STATUS_MESSAGES = {};

/**
 * Shows status message.
 * 
 * @param {mixed} code
 */
function showStatusMsg(code) {
  $('div.alert-' + code).slideDown(500);
}

/**
 * Hides status message.
 * 
 * @param {int} time
 * @param {mixed} code
 */
function hideStatusMsg(time, code) {
  time = ((typeof time === 'undefined') ? false : time);
  
  if (TIMER_FOR_STATUS_MESSAGES[code] !== null) clearTimeout(TIMER_FOR_STATUS_MESSAGES[code]);
  if (time) {
    $('div.alert-' + code).slideUp(time, function() { $(this).remove(); });
  } else {
    $('div.alert-' + code).hide(0, function() { $(this).remove(); });
  }  
}

/**
 * Hides status messages.
 * 
 * @param {type} time
 */
function hideStatusMsgs(time) {
  for (var code in TIMER_FOR_STATUS_MESSAGES) {
    hideStatusMsg(time, code);
  }
}

/**
 * Higlights inputs containing message.
 * 
 * @param {string} message
 * @param {string} classMsg
 */
function higlightInputs(message, classMsg) {
  $(document).ready(function() {
    var $message = $(message);
  
    $message.find('li').each(function() {
      var $li = $(this);
      if (typeof $li.attr('data-key') !== 'undefined') {
        var $input = $('input[name="' + $li.data('key') + '"]');

        if ($li.data('key') && $input.length) {
          $input.closest('.control-group').addClass(classMsg);
        }
      }
    });
  });
}

/**
 * Toggle status message.
 * 
 * @param {string} messages
 */
function getStatusMessage(messages) {
  hideStatusMsg();  
  var getClass = function(status) {
    var classMsg;
    switch (status) {
      case 'msg_201': classMsg = 'info';      break;
      case 'msg_400': classMsg = 'error';     break;
      case 'msg_417': classMsg = 'warning';   break;
      case 'msg_200': classMsg = 'success';   break;
      case 'msg_500':
      default:        classMsg = 'error';     break;
    }
    
     return classMsg;
  };
  
  var $messages = $.parseJSON(messages);
  if ($messages) {
    var $messageContainer = $('div#messages_container');
    for (var code in $messages) {
      var htmlMessage = $messages[code];
      var className = getClass(code);
      
      higlightInputs(htmlMessage, className);
      
      var $messageDiv = $('<div />').addClass('alert')
                                    .addClass('alert-' + className)
                                    .attr('data-identifier', className)
                                    .mouseenter(function() {
                                      var $self = $(this);
                                      clearTimeout(TIMER_FOR_STATUS_MESSAGES[$self.data('identifier')]);
                                    })
                                    .mouseleave(function() {
                                      var $self = $(this);
                                      TIMER_FOR_STATUS_MESSAGES[$(this).data('identifier')] = setTimeout(function() {
                                        hideStatusMsg(200, $self.data('identifier'));
                                      }, 5000);
                                    })
                                    .css({display: 'none'});
      $('<button />').addClass('close')
                     .attr('data-dismiss', 'alert')
                     .appendTo($messageDiv);
      $('<span />').html(htmlMessage).appendTo($messageDiv);
      $messageDiv.appendTo($messageContainer);
      
      showStatusMsg(className);
      TIMER_FOR_STATUS_MESSAGES[className] = setTimeout(function() {
        hideStatusMsgs(200);
      }, 5000);
    }
  }
}

var Menu = {
  setActiveMenuPath: function() {
    $('.page-sidebar-menu li.active').each(function() {
      var current = parseInt($(this).data('parent'));
      while (current > 0) {
        var $parent = $('li[data-id="' + current + '"]');
        $parent.addClass('active');
        current = parseInt($parent.data('parent'));
      }
    });
  }
};

var ProSYS = {
  /**
   * Initialize TinyMCE editor
   */
  InitTinyMCE: function() {
    tinymce.init({
      mode: 'textareas',
      selector: 'textarea.tinymce',
      fontsize_formats: "8px 10px 12px 14px 16px 20px 36px 48px 92px 110px",
      theme: "modern",
      plugins: [
        "eqneditor advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        "save table contextmenu directionality emoticons template paste textcolor responsivefilemanager previewhtml"
      ],
      width: '100%',
      toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
      toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media responsivefilemanager code | insertdatetime preview | forecolor backcolor",
      toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
      //toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons',
      //toolbar1: 'previewhtml fullscreen | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | cut copy paste pasteword | undo redo',
      //toolbar2: 'searchreplace | image media responsivefilemanager | forecolor backcolor | charmap eqneditor',
      //toolbar3: '',
      //toolbar4: 'ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft | ',
      image_advtab: true,
      menubar: false,
      style_formats: [
        {title: 'Odstavec', block: 'p'},
        {title: 'Nadpis 1', block: 'h1'},
        {title: 'Nadpis 2', block: 'h2', classes: 'section'},
        {title: 'Nadpis 3', block: 'h3', classes: 'underline'},
        {title: 'Nadpis 4', block: 'h4'},
        {title: 'Tučný text', inline: 'b'}
      ],
      language : 'cs',
      entity_encoding : 'raw',
      entities : '160,nbsp',

      // external file manager
      external_filemanager_path: '/admin/resources/js/plugins/filemanager/',
      filemanager_title: 'Správce souborů',
      external_plugins: { "filemanager" : '/admin/resources/js/plugins/filemanager/plugin.min.js'}
    });
    
    // minimalisticke TinyMCE
    tinymce.init({
      mode: 'textareas',
      selector: 'textarea.tinymce_mini',
      fontsize_formats: "10px 12px 14px",
      theme: "modern",
      plugins: [
        "eqneditor advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        "save table contextmenu directionality emoticons template paste textcolor responsivefilemanager previewhtml"
      ],
      width: '100%',
      toolbar1: "undo redo | bold italic underline | forecolor backcolor | fontsizeselect | bullist numlist outdent indent | link unlink | code",
      image_advtab: true,
      menubar: false,
      language : 'cs',
      entity_encoding : 'raw',
      entities : '160,nbsp',

      // external file manager
      external_filemanager_path: '/admin/resources/js/plugins/filemanager/',
      filemanager_title: 'Správce souborů',
      external_plugins: { "filemanager" : '/admin/resources/js/plugins/filemanager/plugin.min.js'}
    });
  }
};

var Lightbox = {
  $tpl: {            
    error    : '<p class="fancybox-error">Cíl nemohl být načten.<br/>Prosím zkuste to později.</p>',
    closeBtn : '<a class="fancybox-item fancybox-close" href="javascript:void(0);" title="Zavřít"></a>',
    next     : '<a class="fancybox-nav next" href="javascript:void(0);" title="Další"><span></span></a>',
    prev     : '<a class="fancybox-nav prev" href="javascript:void(0);" title="Předchozí"><span></span></a>'
  },
  
  init: function() {
    // lightbox  
    $('.fancybox').fancybox({
      maxWidth    : '80%',
      maxHeight   : '90%',
      fitToView   : false,
      width       : '70%',
      height      : '70%',
      autoSize    : true,
      closeClick	: false,
      openEffect	: 'fade',
      closeEffect	: 'fade',
      tpl         : this.$tpl,
      wrapCSS     : 'fancy',
      afterLoad   : function() {
        var callbackOpen = this.element.data('callback-open');
        if (typeof window[callbackOpen] !== 'undefined') {
          window[callbackOpen]();
        }
      },
      afterClose  : function() {
        var callbackClose = this.element.data('callback-close');
        if (typeof window[callbackClose] !== 'undefined') {
          window[callbackClose]();
        }
      },
      beforeChange: function() {
        debug.log('nacetl se dalsi obrazek');
      }
    });
    
    $('.fancybox-iframe').fancybox({
      maxWidth    : '80%',
      maxHeight   : '90%',
      fitToView   : false,
      minWidth    : '800px',
      minHeight   : '500px',
      autoSize    : true,
      closeClick	: false,
      openEffect	: 'fade',
      closeEffect	: 'fade',
      tpl         : this.$tpl,
      wrapCSS     : 'fancy',
      type        : 'iframe'
    });
  }
};

$(document).ready(function() {
  // prevent default behaviour of the blind links
  $('body').on('click', '[href="#"]', function(e) {
    e.preventDefault();
  });
  
  App.init(); // initlayout and core plugins
  Index.init();
  //Index.initJQVMAP(); // init index page's custom scripts
  Index.initCalendar(); // init index page's custom scripts
  //Index.initCharts(); // init index page's custom scripts
  //Index.initChat();
  //Index.initMiniCharts();
  Index.initDashboardDaterange();
  //Index.initIntro();
  Menu.setActiveMenuPath();
  FormComponents.init();
  ProSYS.InitTinyMCE();
  Lightbox.init();
  
  // udela radky spravovatelne tabulky klikatelne
  $('.manage-table tbody tr').click(function(event) {
    var $target = $(event.target);

    $target.css({
      '-webkit-touch-callout': 'none',
      '-webkit-user-select': 'none',
      '-khtml-user-select': 'none',
      '-moz-user-select': 'none',
      '-ms-user-select': 'none',
      'user-select': 'none'
    });
    
    setTimeout(function() {
      $target.css({
        '-webkit-touch-callout': 'text',
        '-webkit-user-select': 'text',
        '-khtml-user-select': 'text',
        '-moz-user-select': 'text',
        '-ms-user-select': 'text',
        'user-select': 'text'
      });
    }, 1000);
  });
  
  /**
   * Obsluhuje dvojklik na řádek.
   */
  $('.manage-table tbody tr:not(.no_manage_row)').dblclick(function(event) {
    var $row = $(this);
    var $target = $(event.target);
    
    if (!$target.is('a') && $target.closest('a').length === 0) {
      var $link = $row.find('.default_action:first-child').closest('a');
      if ($link.length) {
        document.location.href = $link.attr('href');
      }
    }

    event.stopPropagation();
  });
});