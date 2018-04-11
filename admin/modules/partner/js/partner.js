/**
 * Vygeneruje hash kod pro firmu.
 * 
 * @param {jQuery} $input
 */
function generateHashCode($input) {
  var params = {
    controller: 'partner',
    action: 'generateHashCode'
  };

  $.get('index.php', params, function(httpResponse) {
    if (httpResponse.response.status === 200) {
      $input.val(httpResponse.data.hashCode);
      $('#partner_styleplus_id').focus();
    }
  }, 'json');
}

var StyloveKoupelnyAutocomplete = {
  selector: {
    company: 'input#partner_styleplus',
    company_id: 'input#partner_styleplus_id'
  },
  
  identification: function(company, html, term) {
    if (company) {
      html = ((typeof html === 'undefined') ? true : html);
      term = ((typeof html === 'undefined') ? false : term);
      
      var identification = company.label + ' ' + ((html) ? '<i>' : '') + '(' + company.id + ')' + ((html) ? '</i>' : '');
      return ((term && html) ? identification.replace(new RegExp('(' + term + ')', 'i'), '<span class="highlight">$1</span>') : identification) +
                ' ' + ((html) ? '<img class="right" src="http://service.styleplus.cz/admin/resources/images/flags/' + company.country.toLowerCase() + '.png" />' : '[' + company.country + ']');
    } else {
      return '';
    }
  },
  
  setValue: function(ui) {
    if (ui.item) {
      ui.item.value = StyloveKoupelnyAutocomplete.identification(ui.item, false);
      $('#partner_styleplus_id').val(ui.item.id);
    }
  },
  
  autocompleteCompany: function() {
    // autocomplete firmy ze Stylovych Koupelen
    var $input = $(this.selector.company);

    // input find autocomplete
    $input.autocomplete({
      source: function(request, response) {
        var data = {
          controller: 'Partner',
          action: 'loadSkCzCompanyNames',
          term: request.term
        };
        
        $.get('index.php', data, function(data) {
          response(data.slice(0, 15));
        }, 'json').fail(function(error) { debug.log(error); });
      },
      
      minLength: 1,
      delay: 100,
      html: true,
      
      focus:  function(event, ui) { StyloveKoupelnyAutocomplete.setValue(ui); },
      select: function(event, ui) { StyloveKoupelnyAutocomplete.setValue(ui); },
      change: function(event, ui) { StyloveKoupelnyAutocomplete.setValue(ui); }
    }).autocomplete('instance')._renderItem = function(ul, item) {
      return $('<li />').append(StyloveKoupelnyAutocomplete.identification(item, true, this.term))
                        .appendTo(ul);
    };;
  },
  
  init: function() {
    this.autocompleteCompany();
  }
};

function pad(number, length) {
  var str = '' + number;
  while (str.length < length) {
    str = '0' + str;
  }

  return str;
}

function setDownloadLinkAge(type, value) {
  $('.downloadXmlFeed').each(function() {
    var $link = $(this);
    var href = $link.attr('href').replace(/&xml_feed_age_days=\d+/, '')
                                 .replace(/&xml_feed_age_date=\d{4}-\d{2}-\d{2}/, '');
    
    $link.attr('href', href);
    
    if (type !== 'infinite') {
      $link.attr('href', href + '&' + type + '=' + value);
    }
    
    switch (type) {
      case 'xml_feed_age_days':
        $link.attr('title', 'Stáhnout XML feed (výrobky změněné za posledních ' + value + ' dní)');
      break;
      
      case 'xml_feed_age_date':
        $link.attr('title', 'Stáhnout XML feed (výrobky změněné od data ' + value + ')');
      break;
      
      default:
        $link.attr('title', 'Stáhnout kompletní XML feed');
      break;
    }
  });
}

/**
 * Obluha zobrazeni linku partnera s excelem
 */
var handleShowLink = function() {
  var $modal = '';
  /**
   * Zobrazi modal okno s linkem na feed partnera
   * @param {jQuery} $partner
   */
  function showLink($partner) {
    $modal.find('.partner_name').text($partner.data('partner-name'));
    var $input = $modal.find('[data-all-address]');
    $input.val($input.data('all-address') + $partner.data('partner-identification')).click(function() {
      $(this).select();
    });
    
    $modal.modal('show');
  }
  
  return {
    init: function() {
      $modal = $('#partner_link_dialog');
      
      $('img.excel').click(function() {
        showLink($(this).closest('tr'));
      });
      
      $('body').on('shown.bs.modal', '#partner_link_dialog', function (e) {
        $(this).find('[data-all-address]').focus().select();
      });
    }
  };
}();

$(document).ready(function() {
  // generator hash kodu
  $('.action-generate-hash-code').click(function(event) {
    event.preventDefault();
    generateHashCode($(this).closest('.controls').find('input'));
  });
  
  // zobrazeni inputu pro nastaveni profitu
  $('input[name="producers[]"]').change(function() {
    var $input = $(this);
    var $label = $input.closest('label');

    if ($input.is(':checked')) {
      $label.children('.profit').fadeIn(400, function() {
        $(this).find('input').focus();
      });
    } else {
      $label.children('.profit').fadeOut(200, function() {
        $(this).find('input').val('');
      });
    }
  });
  
  // inicializuje autocomplete Stylovych koupelen
  StyloveKoupelnyAutocomplete.init();
  
  // nastaveni stari stahovaneho feedu
  $('.btn-group .dropdown-menu a').click(function() {
    var $link = $(this);
    var $actions = $(this).closest('.actions');
    var $input = $actions.find('span.input');
    
    if ($link.is('.age_days')) {
      $input.html('<span class="input"><input class="mini number" type="number" value="7" min="1" /></span>');
      $input.find('input').change();
    } else if ($link.is('.age_date')) {
      var date = new Date()
      date.setDate(date.getDate() - 7);

      $input.html('<span class="input form_date"><input class="mini" type="text" readonly="readonly" data-dtp-date-format="yy-mm-dd" data-dtp-show-button-panel="false" data-dtp-max-date="-1d" /></span>')
            .find('input').val(date.getFullYear() + '-' + pad((date.getMonth() + 1), 2) + '-' + pad(date.getDate(), 2))
                          .change();
    
      FormComponents.handlejQueryUIDatePickers();
    } else {
      $input.find('input').prop('readonly', false)
                          .prop('disabled', true);

      setDownloadLinkAge('infinite', null);
    }
  });
  
  $('.portlet-title .actions span.input').on('change', 'input', function() {
    var $input = $(this);
    
    if ($input.is('[type="number"]')) {
      setDownloadLinkAge('xml_feed_age_days', $input.val());
    } else {
      setDownloadLinkAge('xml_feed_age_date', $input.val());
    }
  });
  
  handleShowLink.init();
});