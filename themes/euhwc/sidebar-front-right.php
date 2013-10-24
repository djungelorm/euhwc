<?php
/**
 * The front page right column widget area.
 *
 * If there are no active widgets, they will be hidden.
 */

if ( ! is_active_sidebar( 'front-right' ) )
  return;
?>

<div class="widget-area">
  <?php if ( is_active_sidebar( 'front-right' ) ) : ?>
    <?php dynamic_sidebar( 'front-right' ); ?>
  <?php endif; ?>
</div><!-- .widget-area -->
