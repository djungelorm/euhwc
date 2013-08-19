<?php
/**
 * The floating sidebar containing the floating widget area.
 *
 * If no active widgets, they will be hidden completely.
 */

if ( is_active_sidebar( 'sidebar-floating' ) ) : ?>

  <script type="text/javascript">
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
  </script>

  <div id="sidebar-floating-visible" class="sidebar-container sidebar-floating" role="complementary">
    <div class="sidebar-inner">
      <a href="#" alt="hide" title="Hide" id="sidebar-floating-hide" class="sidebar-floating-button genericon genericon-collapse"></a>
      <div class="widget-area">
        <?php dynamic_sidebar( 'sidebar-floating' ); ?>
      </div>
    </div>
  </div>

  <div class="sidebar-floating" id="sidebar-floating-hidden">
    <a href="#" alt="show" title="Show" id="sidebar-floating-show" class="sidebar-floating-button genericon genericon-expand"></a>
  </div>

<?php endif; ?>
