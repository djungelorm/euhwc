<?php
/**
 * The sidebar containing the front page widget areas.
 *
 * If no active widgets in either sidebar, they will be hidden completely.
 */

if ( ! is_active_sidebar( 'front-left' ) &&
     ! is_active_sidebar( 'front-middle' ) &&
     ! is_active_sidebar( 'front-right' ) )
  return;

?>

<div id="secondary" class="sidebar-container front-page-template" role="complementary">

  <?php if ( is_active_sidebar( 'front-left' ) ) : ?>
    <div class="widget-area">
      <?php dynamic_sidebar( 'front-left' ); ?>
    </div><!-- .widget-area -->
  <?php endif; ?>

  <?php if ( is_active_sidebar( 'front-middle' ) ) : ?>
    <div class="widget-area">
      <?php dynamic_sidebar( 'front-middle' ); ?>
    </div><!-- .widget-area -->
  <?php endif; ?>

  <?php if ( is_active_sidebar( 'front-right' ) ) : ?>
    <div class="widget-area">
      <?php dynamic_sidebar( 'front-right' ); ?>
    </div><!-- .widget-area -->
  <?php endif; ?>

</div><!-- #secondary -->
