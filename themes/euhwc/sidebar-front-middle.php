<?php
/**
 * The front page middle column widget area.
 *
 * If there are no active widgets, they will be hidden.
 */

if ( ! is_active_sidebar( 'front-middle' ) )
  return;
?>

<div class="widget-area">
  <?php if ( is_active_sidebar( 'front-middle' ) ) : ?>
    <?php dynamic_sidebar( 'front-middle' ); ?>
  <?php endif; ?>
</div><!-- .widget-area -->
