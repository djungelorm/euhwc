<?php
/**
 * Template Name: Front Page Template
 */

get_header(); ?>

  <div id="primary" class="content-area front-page-template">
    <div id="content" class="site-content" role="main">

      <?php get_sidebar( 'front-left' ); ?>

    </div><!-- #content -->
  </div><!-- #primary -->

  <div id="tertiary" class="sidebar-container front-page-template" role="complementary">
    <div class="sidebar-inner">
      <div class="widget-area">

        <div class="widget-area image-cycle">
          <aside id="image-cycle-1" class="widget widget_text">
            <?php echo do_shortcode('[image_cycle]'); ?>
          </aside>
        </div>

        <div class="clearboth"></div>

        <?php get_sidebar( 'front-right' ); ?>
        <?php get_sidebar( 'front-middle' ); ?>

        <div class="clearboth"></div>

      </div>
    </div>
  </div><!-- #primary -->

<?php get_footer(); ?>