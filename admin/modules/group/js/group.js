function checkGroup($self, check) {
  var groupName = $self.closest('div.module_name').data('group-id');
  $('input[data-group="' + groupName + '"]').each(function() {
    $(this).prop('checked', check);
    $.uniform.update($(this));
  });
}

function checkParent($self) {
  if ($self.data('parent') > 0) {
    var $newSelf = $('input[data-id="' + $self.data('parent') + '"]');
    $newSelf.prop('checked', true);
    $.uniform.update($newSelf);
    checkParent($newSelf);
  }
}

function uncheckChildren($self) {
  var $child = $self.closest('li').find('input[data-parent="' + $self.data('id') + '"]');
  if ($child.length) {
    $child.prop('checked', false);
    $.uniform.update($child);
    $child.each(function() {
      uncheckChildren($(this));
    });
  }
}

$(document).ready(function() {
  $('div.module_name.check_all').each(function() {
    var $self = $(this);
    var $checkAll = $('<span />').addClass('check_all').html('označit vše');
    var $uncheckAll = $('<span />').addClass('uncheck_all').html('zrušit vše');
    var $controls = $('<span />').addClass('controls_checker').html('(' + $checkAll[0].outerHTML + ' / ' + $uncheckAll[0].outerHTML + ')');
    
    $self.html($self.html() + ' ' + $controls[0].outerHTML);
  });
  
  $('body').on('click', 'span.check_all', function() {
    checkGroup($(this), true);
  });
  
  $('body').on('click', 'span.uncheck_all', function() {
    checkGroup($(this), false);
  });
  
  $('input[name="group_rights[]"]').click(function() {
    var $self = $(this);
    if ($self.is(':checked')) {
      checkParent($self);
    } else {
      uncheckChildren($self);
    }
  });
});