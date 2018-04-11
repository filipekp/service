var INPROCESS_TIMER;
(function($){
  $.fn.extend({
    inprocess: function(options) {
      /**
       * Default options.
       * @type object
       */
      var defaults = {
        message: '',
        loadingImage: 'js/plugins/inprocess/loading-big.gif'
      };

      /**
       * Extends default options of given options
       * @type @exp;$@call;extend
       */
      options = $.extend(defaults, options);
      
      var counter = function() {
        var $c = $('#inprocess_counter');

        var $min = $c.find('.min');
        var $sec = $c.find('.sec');

        var minInt = parseInt($min.text());
        var secInt = parseInt($sec.text());

        if (secInt === 59) {
          $sec.text('00');

          minInt++;
          $min.text(((minInt < 10) ? '0' : '') + minInt);
        } else {
          secInt++;
          $sec.text(((secInt < 10) ? '0' : '') + secInt);
        }

        INPROCESS_TIMER = setTimeout(counter, 1000);
      };
      
      /**
       * Create lightbox with "in process" message.
       */
      return this.each(function() {        
        /* BODY */
        var $body = $('body');

        /* PRELOAD LOADING IMAGE */
        (new Image()).src = options.loadingImage;
        $('<img src="' + options.loadingImage + '" />').css({
          width: '1px',
          height: '1px',
          position: 'absolute',
          left: '-9999px',
          top: '-9999px'
        }).appendTo($body);
        
        /* HANDLE CLICK EVENT */
        $(this).on('click', function() {
          /* MAIN WRAPPER */
          var $wrapper = $('<div id="inprocess_wrapper" />').appendTo($body);

          /* SHOW LOADING LIGHTBOX */
          $('<div />').css({
            position: 'fixed',
            left: 0,
            top: 0,
            right: 0,
            bottom: 0,
            zIndex: 9000,
            backgroundColor: 'rgba(0, 0, 0, 0.8)'
          }).appendTo($wrapper);
          
          /* SHOW CONTENT */
          var $content = $('<div />').css({
            float: 'left'
          }).appendTo($wrapper);

          // message
          $('<span>' + options.message + '</span>').css({
            fontSize: '18px',
            fontWeight: 'bold',
            color: '#ffffff'
          }).appendTo($content);

          // new row
          $('<br />').appendTo($content);

          // image
          $('<img src="' + options.loadingImage + '" width="128" height="128" />').css('margin-top', '50px').appendTo($content);
          
          /* CHANGE POSITION OF THE CONTENT */
          $content.css({
            position: 'fixed',
            left: '50%',
            top: '50%',
            marginLeft: '-' + ($content.width() / 2) + 'px',
            marginTop: '-' + ($content.height() / 2) + 'px',
            zIndex: 9999,
            textAlign: 'center'
          });

          /* SHOW COUNTER */
          $('<b id="inprocess_counter"><span class="min">00</span>:<span class="sec">00</span></b>').css({
            position: 'fixed',
            right: '20px',
            top: '20px',
            color: '#ffffff',
            fontSize: '15px',
            zIndex: 9999
          }).appendTo($wrapper);

          /* PLAY COUNTER */
          counter();
        });
      });
    }
  });
})(jQuery);
