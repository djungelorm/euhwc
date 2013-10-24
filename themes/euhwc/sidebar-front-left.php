<?php
/**
 * The front page left column widget area.
 *
 * If there are no active widgets, they will be hidden.
 */

if ( ! is_active_sidebar( 'front-left' ) )
  return;
?>

<div class="widget-area">

  <?php /* The loop */ ?>
  <?php while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="entry-content front-page-template">
        <?php the_content(); ?>
      </div><!-- .entry-content -->
     </article><!-- #post -->

  <?php endwhile; ?>

  <?php if ( is_active_sidebar( 'front-left' ) ) : ?>
    <?php dynamic_sidebar( 'front-left' ); ?>
  <?php endif; ?>

</div><!-- .widget-area -->
