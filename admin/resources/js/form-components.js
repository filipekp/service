var FormComponents = function () {
    var handleWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5({
                "stylesheets": ["assets/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            });
        }
    }

    var resetWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5({
                "stylesheets": ["assets/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            });
        }
    }

    var handleToggleButtons = function () {
        if (!jQuery().toggleButtons) {
          return;
        }
        
        $('.basic-toggle-button').toggleButtons({
          label: { enabled: "ZAP", disabled: "VYP" }
        });
        
        $('.yesno-toggle-button').toggleButtons({
          label: { enabled: "ANO", disabled: "NE" },
          style: { enabled: "success", disabled: "danger" }
        });
        
        $('.text-toggle-button').toggleButtons({
          width: 200,
          label: { enabled: "Lorem Ipsum", disabled: "Dolor Sit" }
        });
        
        $('.danger-toggle-button').toggleButtons({
          // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
          style: { enabled: "danger", disabled: "info" }
        });
        
        $('.info-toggle-button').toggleButtons({
          style: { enabled: "info", disabled: "" }
        });
        
        $('.success-toggle-button').toggleButtons({
          style: { enabled: "success", disabled: "info" }
        });
        
        $('.warning-toggle-button').toggleButtons({
          style: { enabled: "warning", disabled: "info" }
        });

        $('.height-toggle-button').toggleButtons({
          height: 100,
          font: { 'line-height': '100px', 'font-size': '20px', 'font-style': 'italic' }
        });
        
        $('.custom-toggle-button').each(function() {
          var $button = $(this);
          var options = {
            label: { enabled: "ZAP", disabled: "VYP" },
            style: { enabled: "", disabled: "" }
          };
          
          if (typeof $button.attr('data-text-enabled') !== 'undefined' && $button.data('text-enabled')) { options.label.enabled = $button.data('text-enabled'); }
          if (typeof $button.attr('data-text-disabled') !== 'undefined' && $button.data('text-disabled')) { options.label.disabled = $button.data('text-disabled'); }
          
          if (typeof $button.attr('data-style-enabled') !== 'undefined' && $button.data('style-enabled')) { options.style.enabled = $button.data('style-enabled'); }
          if (typeof $button.attr('data-style-disabled') !== 'undefined' && $button.data('style-disabled')) { options.style.disabled = $button.data('style-disabled'); }
          
          if (typeof $button.attr('data-width') !== 'undefined' && $button.data('width')) { options.width = $button.data('width'); }
          if (typeof $button.attr('data-height') !== 'undefined' && $button.data('height')) { options.height = $button.data('height'); }

          $button.toggleButtons(options);
        });
    };

    var handleBootstrapSwitch = function () {
      if (!jQuery().bootstrapSwitch) {
        return;
      }

      $('.basic-bootstrap-switch').bootstrapSwitch({
        onText: 'ZAP',
        offText: 'VYP',
        size: 'small'
      });

      $('.mini-bootstrap-switch').bootstrapSwitch({
        onText: 'ZAP',
        offText: 'VYP',
        size: 'mini'
      });

      $('.custom-bootstrap-switch').bootstrapSwitch({
        onInit: function(event, state) {
          var $target = $(event.target);
          var $wrapper = $target.closest('.bootstrap-switch-wrapper');

          if (typeof $target.attr('data-width') !== 'undefined' && $target.data('width')) {
            $wrapper.css('width', $target.data('width') + 'px');
          }

          if (typeof $target.attr('data-height') !== 'undefined' && $target.data('height')) {
            $wrapper.css('height', $target.data('height') + 'px');
          }
        }
      });
    };

    var handleTagsInput = function () {
        if (!jQuery().tagsInput) {
            return;
        }
        $('#tags_1').tagsInput({
            width: 'auto',
            'onAddTag': function () {
                //alert(1);
            },
        });
        $('#tags_2').tagsInput({
            width: 240
        });
    }

    var handlejQueryUIDatePickers = function () {
        var $defaultSettings = {
          closeText: 'Zavřít',
          prevText: 'Předchozí',
          nextText: 'Další',
          currentText: 'Nyní',
          monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
                       'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
          monthNamesShort: ['Led', 'Úno', 'Bře', 'Dub', 'Květ', 'Červ',
                            'Črvnc', 'Srp', 'Září', 'Říj', 'List', 'Pro'],
          dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
          dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
          dayNamesMin: ['N', 'P', 'Ú', 'S', 'Č', 'P', 'S'],
          weekHeader: 'Týd',
          dateFormat: 'dd.mm.yy',
          timeFormat: "HH:mm:ss",
          firstDay: 1,
          isRTL: false,
          showMonthAfterYear: false,
          yearSuffix: '',
          showWeek: false,
          showAnim: "slideDown",
          showButtonPanel: true,

          // add button for clear input with date
          beforeShow: function(input) {
            setTimeout(function() {
              var buttonPanel = $(input)
                .datepicker('widget')
                .find('.ui-datepicker-buttonpane');

              $('<button>', {
                'class': 'ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all',
                text: 'Vymazat',
                click: function() {
                  $.datepicker._clearDate(input);
                }
              }).appendTo(buttonPanel);
            }, 1);
          }
        };
      
      var initializeDatepicker = function () {
        var $input = $('.form_date > input');
        var options = {};
        
        var data = $input.data();
        for (var prop in data) {
          if (prop.indexOf('dtp') === 0) {
            var option = prop.charAt(3).toLowerCase() + prop.substr(4);
            options[option] = data[prop];
          }
        }
        
        $('.form_date > input').datepicker($.extend({}, $defaultSettings, options));
      };
      
      return {
        init: initializeDatepicker
      };
    }();

    var handleColorPicker = function () {
        if (!jQuery().colorpicker) {
            return;
        }
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        $('.colorpicker-rgba').colorpicker();
    };

    var handleSelect2 = function () {
      $('.select2').select2(FormComponents.defaultSelect2);
    };

    var handleMultiSelect = function () {
        $('#my_multi_select1').multiSelect();
        $('#my_multi_select2').multiSelect({
            selectableOptgroup: true
        });        
    };

    var handleInputMasks = function () {
        $.extend($.inputmask.defaults, {
            'autounmask': true
        });

        $("#mask_date").inputmask("d/m/y", {autoUnmask: true});  //direct mask        
        $("#mask_date1").inputmask("d/m/y",{ "placeholder": "*"}); //change the placeholder
        $("#mask_date2").inputmask("d/m/y",{ "placeholder": "dd/mm/yyyy" }); //multi-char placeholder
        $("#mask_phone").inputmask("mask", {"mask": "(999) 999-9999"}); //specifying fn & options
        $("#mask_tin").inputmask({"mask": "99-9999999"}); //specifying options only
        $("#mask_number").inputmask({ "mask": "9", "repeat": 10, "greedy": false });  // ~ mask "9" or mask "99" or ... mask "9999999999"
        $("#mask_decimal").inputmask('decimal', { rightAlignNumerics: false }); //disables the right alignment of the decimal input
        $("#mask_currency").inputmask('€ 999.999.999,99', { numericInput: true });  //123456  =>  € ___.__1.234,56
       
        $("#mask_currency2").inputmask('€ 999,999,999.99', { numericInput: true, rightAlignNumerics: false, greedy: false}); //123456  =>  € ___.__1.234,56
        $("#mask_ssn").inputmask("999-99-9999", {placeholder:" ", clearMaskOnLostFocus: true }); //default
    };

    var handleIPAddressInput = function () {
        $('#input_ipv4').ipAddress();
        $('#input_ipv6').ipAddress({v:6});
    };

    return {
        // vychozi nastaveni pro select2
        defaultSelect2: {
          formatNoMatches: function () { return "Nenalezeny žádné položky"; },
          formatInputTooShort: function (input, min) {
              var n = min - input.length;
              if (n === 1) {
                  return "Prosím zadejte ještě jeden znak";
              } else if (n <= 4) {
                  return "Prosím zadejte ještě další " + n + " znaky";
              } else {
                  return "Prosím zadejte ještě dalších " + n + " znaků";
              }
          },
          formatInputTooLong: function (input, max) {
              var n = input.length - max;
              if (n === 1) {
                  return "Prosím zadejte o jeden znak méně";
              } else if (n <= 4) {
                  return "Prosím zadejte o " + n + " znaky méně";
              } else {
                  return "Prosím zadejte o " + n + " znaků méně";
              }
          },
          formatSelectionTooBig: function (limit) {
              if (limit === 1) {
                  return "Můžete zvolit jen jednu položku";
              } else if (limit <= 4) {
                  return "Můžete zvolit maximálně " + n + " položky";
              } else {
                  return "Můžete zvolit maximálně " + limit + " položek";
              }
          },
          formatLoadMore: function (pageNumber) { return "Načítají se další výsledky…"; },
          formatSearching: function () { return "Vyhledávání…"; },
          allowClear: true
        },
        
        handleBootstrapSwitch: handleBootstrapSwitch,
        handlejQueryUIDatePickers: handlejQueryUIDatePickers.init,
          
        //main function to initiate the module
        init: function () {
          //handleWysihtml5();
          handleToggleButtons();
          handleBootstrapSwitch();
          /*handleTagsInput();*/
          handlejQueryUIDatePickers.init();
          //handleClockfaceTimePickers();
          //handleColorPicker();
          handleSelect2();
          /*handleInputMasks();
          handleIPAddressInput();
          handleMultiSelect();

          App.addResponsiveHandler(function(){
              resetWysihtml5();
          })*/
        }

    };

}();