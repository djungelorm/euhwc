<?php
/**
 * The floating sidebar containing the floating widget area.
 *
 * If no active widgets, they will be hidden completely.
 */

if ( is_active_sidebar( 'sidebar-floating' ) ) : ?>

  <?php  wp_enqueue_script('sidebar-floating', get_stylesheet_directory_uri().'/js/sidebar-floating.js'); ?>

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
