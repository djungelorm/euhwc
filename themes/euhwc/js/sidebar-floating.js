jQuery(document).ready(function($) {
  $('#sidebar-floating-hide')
    .click(function() {
      $('#sidebar-floating-visible').hide(200);
      $('#sidebar-floating-hidden').show();
      sessionStorage.setItem('sidebar-floating', 'hidden');
    });
  $('#sidebar-floating-show')
    .click(function() {
      $('#sidebar-floating-hidden').hide();
      $('#sidebar-floating-visible').show(200);
      sessionStorage.setItem('sidebar-floating', 'visible');
    });
  var state = sessionStorage.getItem('sidebar-floating');
  if (state == 'hidden') {
    $('#sidebar-floating-hidden').show();
    $('#sidebar-floating-visible').hide();
  }
});
