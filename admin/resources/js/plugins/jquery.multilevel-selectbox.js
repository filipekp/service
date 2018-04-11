/**
 * jQuery plugin multilevelSelectbox.<br />
 * Creates selectbox from given unordered list.
 * 
 * @param {jQuery} $
 */
(function($) {
  $.fn.multilevelSelectbox = function(options) {
    // settings
    var settings = $.extend(true, {
      dynamic: '',                                                          // selector (the same as multilevelSelectbox selector) - controls dynamic creation of new selectboxes
      class: 'multilevel_selectbox',                                        // string
      prefix: 'multilevel_selectbox_',                                      // string
      
      openSpeed: 400,                                                       // int
      closeSpeed: 400,                                                      // int
      
      disabling: {                                                          // false, { table: function($selectbox), value: function($row) }
        table: function($selectbox) {
          return $('#' + $selectbox.data('mlselectbox-table'));
        },
        
        value: function($row) {
          return $row.data('mlselectbox-value');
        }
      },
      multiselect: false
    }, options);
    
    // prefixes of internal identifiers
    var wrapperPrefix = settings.prefix + 'wrapper_';             // multilevel selectbox wrapper
    var inputPrefix = settings.prefix;                            // hidden input that stores the value of selected option
    var labelPrefix = settings.prefix + 'label_';                 // clickable label - shows selected option
    var listWrapperPrefix = settings.prefix + 'list_wrapper_';    // options list wrapper
    var listPrefix = settings.prefix + 'box_';                    // options list
    var resetPrefix = settings.prefix + 'reset_';                 // reset button
    
    // index increment when there is any "prefixed wrapper id"
    var increment = 0;
    $('[id^="' + wrapperPrefix + '"]').each(function() {
      var current = parseInt($(this).attr('id').substring(wrapperPrefix.length));
      if (current > increment) {
        increment = current;
      }
    });
    increment++;
    
    /**
     * Checks whether the selectbox is disabled or not.
     * 
     * @param {object} $selectbox jQuery object
     * @returns {Boolean}
     */
    var isDisabled = function($selectbox) {
      return (typeof $selectbox.attr('data-disabled') !== 'undefined') && ($selectbox.data('disabled') === 'disabled');
    };
    
    /**
     * Opens selectbox.
     * @param {Integer} index
     */
    var open = function(index) {
      var $listWrapper = $('#' + listWrapperPrefix + index);
      var $label = $listWrapper.parent().find('#' + labelPrefix + index);
      
      // add class of the clickable label
      $label.addClass('opened');
      
      // slide down and focus search box
      $listWrapper.slideDown(settings.openSpeed, function() {
        $listWrapper.find('.search input').focus();
      });
      
      var left = $listWrapper.css('left');
      if ((($listWrapper.offset().left + $listWrapper.outerWidth(true)) > $(window).width() && left === '0px') || left === 'auto') {
        $listWrapper.css({
          left: 'auto',
          right: '0'
        });
      } else {
        $listWrapper.css({
          left: '0',
          right: 'auto'
        });
      }
    };
    
    var countItems = function(index) {
      return  $('#' + listWrapperPrefix + index).closest('.multilevel_selectbox').find('ul').children().length;
    }
    
    var countSelectedItems = function(index) {
      return  $('#' + listWrapperPrefix + index).closest('.multilevel_selectbox').find('ul').children('.selected').length;
    }
    
    /**
     * Update label in box.
     * @param {Integer} index
     */
    var updateLabel = function(index) {
      var $listWrapper = $('#' + listWrapperPrefix + index);
      var $label = $listWrapper.parent().find('#' + labelPrefix + index);
      
      $label.text(((countSelectedItems(index) === 0) ? $('#' + listWrapperPrefix + index).closest('.multilevel_selectbox').find('ul').data('default-text') : 'vybráno ' + countSelectedItems(index) + ' z ' + countItems(index)));
    };
    
    /**
     * Closes selectbox.
     * @param {Integer} index
     */
    var close = function(index) {
      var $listWrapper = $('#' + listWrapperPrefix + index);
      var $label = $listWrapper.parent().find('#' + labelPrefix + index);
      
      // remove class of the clickable label
      $label.removeClass('opened');
      
      // slide up and reset the search box value
      $listWrapper.slideUp(settings.closeSpeed, function() {
        $listWrapper.find('.search input').val('').keyup();
      });
    };
    
    /**
     * Closes all selectboxes excluding given one.
     * @param {Integer} index
     */
    var closeAll = function(index) {
      index = ((typeof index === 'undefined') ? -1 : index);
      
      $('[id^="' + wrapperPrefix + '"]').each(function(idx) {
        idx += increment;

        if (idx !== index) {
          close(idx);
        }
      });
    };

    /**
     * Handles disabling of selectbox items according to associated table data.<br />
     *  - useful for the selectbox used as "adder"
     *  
     * @param {object} $selectbox jQuery object
     */
    var handleDisabling = function($selectbox) {
      setTimeout(function() {
        // remove class "disabled" of all list items
        var $items = $selectbox.find('li');
        $items.removeClass('disabled')
              .find('>span').css('cursor', 'pointer');

        // add class "disabled" when used in the table
        settings.disabling.table($selectbox).find('>tr, >tbody>tr').each(function() {
          $items.filter('[data-value="' + settings.disabling.value($(this)) + '"]').removeClass('selected')
                                                                                   .addClass('disabled')
                                                                                   .find('>span').css('cursor', 'default');
        });
      }, 100);
    };
      
    /**
     * Close all selectboxes on click on body.
     * @param {Event} event
     */
    $('body').click(function(event) {
      var $target = $(event.target);
      var $multilevelSelectbox = $target.closest('.' + settings.class);

      closeAll(
        (($multilevelSelectbox.length) ?
          parseInt($multilevelSelectbox.attr('id').replace(wrapperPrefix, '')) :
          -1)
      );
    });
    
    /**
     * Following lines should be applied only once.
     */
    if (settings.dynamic) {
      /**
       * Creates new multilevel selectbox on new inserted DOM node
       * @param {object} event
       */
      $('body').on('DOMNodeInserted', function(event) {
        var $element = $(event.target);

        if ($element.is(settings.dynamic)) {
          var newOptions = $.extend({}, options);
          newOptions.dynamic = '';
          
          $element.multilevelSelectbox(newOptions);
        }
      });
    }
    
    /**
     * Goes through every item of the selector.
     * @param {int} index
     */
    return this.each(function(index) {
      var $selectbox = $(this);
      
      if ($selectbox.closest('.' + settings.class).length === 0) {
        // change selectbox default style
        $selectbox.css({
          display: 'inline-block',
          whiteSpace: 'nowrap',
          margin: 0,
          padding: 0
        });
        
        if (!$selectbox.is('[data-disabled]')) {
          $selectbox.attr('data-disabled', '');
        }
        
        // index correction
        index += increment;

        // identifiers
        var wrapperId = wrapperPrefix + index;
        var inputId = inputPrefix + index;
        var labelId = labelPrefix + index;
        var listWrapperId = listWrapperPrefix + index;
        var listId = listPrefix + index;
        var resetId = resetPrefix + index;
      
        /**
         * Handles click on selectbox label.
         */
        var handleClick = function() {
          if ($(this).hasClass('opened')) {
            close(index);
          } else {
            closeAll(index);
            open(index);
          }
        };
      
        /**
         * Disables selectbox.
         */
        var disable = function() {
          if ($selectbox.data('disabled') !== 'disabled') {
            $selectbox.data('disabled', 'disabled');
            $('#' + labelId).addClass('disabled')
                            .css('cursor', 'default')
                            .off('click', handleClick);
            
            // hide reset button
            $('#' + resetId).hide();
          }
        };

        /**
         * Enables selectbox.
         */
        var enable = function() {
          if ($selectbox.data('disabled') === 'disabled') {
            $selectbox.data('disabled', '');
            $('#' + labelId).removeClass('disabled')
                            .css('cursor', 'pointer')
                            .on('click', handleClick);
            
            // show reset button
            $('#' + resetId).show();
          }
        };
    
        /**
         * Resets the selectbox
         */
        var reset = function() {
          $('#' + labelId).text($selectbox.data('default-text'));
          if (!settings.multiselect) {
            $('#' + inputId).val('')
                            .trigger('change');
          } else {
            $selectbox.find('li')
                      .find('input[type="checkbox"]')
                      .prop('checked', false);
          }
          $selectbox.find('.selected').removeClass('selected');
        };
        
        // creates multilevel selectbox wrapper
        var $wrapper = $('<div />').attr('id', wrapperId)
                                   .addClass(settings.class)
                                   .css({
                                     position: (($selectbox.css('position') !== 'static') ? $selectbox.css('position') : 'relative'),
                                     left: (($selectbox.css('left')) ? $selectbox.css('left') : '0'),
                                     top: (($selectbox.css('top')) ? $selectbox.css('top') : '0'),
                                     zIndex: (($selectbox.css('z-index')) ? $selectbox.css('z-index') : '0'),
                                     display: 'inline-block'
                                   });
        
        // wrap selectbox -> "the starting point" from where everything can be changed
        $selectbox.wrap($wrapper);
        $wrapper = $selectbox.parent();

        // creates hidden input storing selected option value
        if (!settings.multiselect) {
          $wrapper.append(
            $('<input />').attr('id', inputId)
                          .attr('type', 'hidden')
                          .attr('name', $selectbox.data('name'))
          );
        }

        // creates clickable label storing selected option show text
        $wrapper.append(
          $('<span />').attr('id', labelId)
                       .addClass('selected_label')
                       .text($selectbox.data('default-text'))
                       .css({
                         display: 'inline-block',
                         whiteSpace: 'nowrap'
                       })
        );

        // add reset button
        var titleReset = 'Vymazat hodnotu';
        if (settings.multiselect) {
          titleReset = 'Vymazat hodnoty';
        }
        $wrapper.append(
          $('<span />').attr('id', resetId)
                       .attr('title', titleReset)
                       .addClass('reset')
                       .text('x')
                       .css({
                         position: 'absolute',
                         right: '20px',
                         top: '5px',
                         color: '#ff0000',
                         fontWeight: 'bold',
                         cursor: 'pointer'
                       })
                       .click(function() {
                         $selectbox.trigger('reset');
                       })
        );

        // creates options list searchbox
        var $searchbox =
          $('<div />').addClass('search')
                      .css('margin-bottom', '5px')
                      .append(
                        $('<input />').attr('type', 'text')
                                      .css({
                                        'box-sizing': 'border-box',
                                        '-moz-box-sizing': 'border-box',
                                        '-ms-box-sizing': 'border-box',
                                        '-webkit-box-sizing': 'border-box',
                                        '-khtml-box-sizing': 'border-box',
                                        width: '100%',
                                        padding: '15px'
                                      })
                                      // suppress enter submit
                                      .on('keyup keypress', function(event) {
                                        var code = event.keyCode || event.which; 
                                        if (code === 13) {               
                                          event.preventDefault();
                                          return false;
                                        }
                                      })
                                      .on('keyup', function() {
                                        // search
                                        var $input = $(this);
                                        var val = $input.val().toLowerCase();

                                        $selectbox.find('span.label').each(function() {
                                          var $span = $(this);

                                          if ($span.text().toLowerCase().indexOf(val) === -1) {
                                            $span.hide();
                                          } else {
                                            $span.show();
                                          }
                                        });
                                      })
                      );
        
        // creates options list itself
        $wrapper.append(
          $('<div />').attr('id', listWrapperId)
                      .addClass('list')
                      .css({
                        position: 'absolute',
                        /*top: $('#' + labelId).outerHeight(),*/
                        left: 0,
                        right: 'auto',
                        zIndex: '1500',
                        marginTop: '3px',
                        padding: '5px',
                        backgroundColor: '#ffffff',
                        minWidth: ($('#' + labelId).outerWidth() - 10) + 'px',    // -10 means list padding - 2 times 5px
                        width: ($selectbox.width() + 10 + 20 + 10 + ((settings.multiselect) ? 25 : 0) + 5) + 'px'     // +10 scrollbar, + 20 inside padding, +10 my padding, +15 checkbox, +5 ???
                      }).append(
                        $searchbox
                      ).append(
                        $('<div />').attr('id', listId)
                                    .css({
                                      padding: '5px',
                                      paddingRight: '15px',
                                      height: (($selectbox.outerHeight() > 250) ? '250px' : 'auto'),
                                      overflow: 'auto'
                                    })
                                    .append($selectbox)
                      ).hide()
        );

        // selectbox - display as block, because of full width
        $selectbox.css('display', 'block');
        
        // repair list items to be clickable
        //  -> wrap content of every list item into the span and then move inside sublist outside of this span
        var $span = $('<span />').css({
                      display: 'block',
                      cursor: 'pointer'
                    }).hover(function() {
                      var $span = $(this);
                      if (!$span.closest('li').hasClass('disabled')) {
                        $span.toggleClass('hover');
                      }
                    }).addClass('label');
        
        $selectbox.find('li').wrapInner($span);
        // přidá checkboxy pokud se jedná o multiselect
        if (settings.multiselect) {
          var indexItem = 0;
          $selectbox.find('li').each(function() {
            var $li = $(this);
            var $span = $li.find('span');
            var inputItemId = inputId + '_' + indexItem;
            var $input = $('<input />').css({
                                         position: 'absolute',
                                         left: '-9999px',
                                         top: '-9999px'
                                       })
                                       .attr('id', inputItemId)
                                       .attr('type', 'checkbox')
                                       .attr('name', $selectbox.data('name') + '[]')
                                       .attr('value', $li.data('value'));
            $span.html($input[0].outerHTML + ' ' + $span.html());
            indexItem++;
          });
        }

        // deepest level without the last one, because the last one has no ul to move
        var deepestLevel = 10;

        for (var level = deepestLevel; level > 0; level--) {
          $selectbox.find('li '.repeat(level)).not('li '.repeat(level + 1)).each(function() {
            var $span = $(this).children('span');
            var $ul = $span.children('ul');

            $ul.insertAfter($span);
          });
        }

        // change cursor when hover the disabled items
        $selectbox.find('li.disabled>span').css('cursor', 'default');
        
        var selectActive = function($active, selected) {
          if ($active.length) {
            var $li = $active.closest('li');

            if (!$li.hasClass('disabled')) {
              if (settings.multiselect) {
                selected = ((typeof selected === 'undefined') ? false : true);
                var $liInput = $li.find('input[type="checkbox"]');
                if (!selected) {
                  $li.toggleClass('selected');
                }
                
                if ($li.hasClass('selected')) {
                  $liInput.prop('checked', true);
                } else {
                  $liInput.prop('checked', false);
                }
                
                updateLabel(index);
              } else {
                $('#' + inputId).val($active.closest('li').data('value'))
                                .trigger('change');
                $('#' + labelId).text((($active.text().length > 30) ? $active.text().substr(0, 28) + '...' : $active.text()));
                
                close(index);
                
                $selectbox.find('.selected').removeClass('selected');
                $li.addClass('selected');
              }
            }
          }
        };

        // make list items to be clickable
        $selectbox.find('span').on('click', function() {
          selectActive($(this));
        });
      
        // in the end set selected value as current
        selectActive($selectbox.find('.selected>span'), true);
    
        // handle disabling of selectbox items according to associated table data
        if (settings.disabling) {
          /**
           * Handle disabling on new row inserted / removed.
           */
          settings.disabling.table($selectbox).on('DOMNodeInserted DOMNodeRemoved', 'tr', function() {
            handleDisabling($selectbox);
          });

          // on the first time load
          handleDisabling($selectbox);
        }
        
        // catches selectbox activity
        $selectbox.on('enable', enable);
        $selectbox.on('disable', disable);
        $selectbox.on('reset', reset);
      
        // handle click event on label - on the first time load
        if (isDisabled($selectbox)) {
          $selectbox.data('disabled', '');            // little bit hack ;-)... it is because of disabled list cannot be disabled again
          $selectbox.trigger('disable');
        } else {
          $selectbox.data('disabled', 'disabled');    // little bit hack ;-)... it is because of enabled list cannot be enabled again
          $selectbox.trigger('enable');
        }
      }
    });
  };
}(jQuery));
