<?php
/**
 * The main front page widget area, that appears in the top right of the fron page.
 *
 * If there are no active widgets, they will be hidden.
 */

if ( is_active_sidebar( 'front-main' ) ) : ?>
	<div id="tertiary" class="sidebar-container front-page-template" role="complementary">
		<div class="sidebar-inner">
			<div class="widget-area">
				<?php dynamic_sidebar( 'front-main' ); ?>
			</div><!-- .widget-area -->
		</div><!-- .sidebar-inner -->
	</div><!-- #tertiary -->
<?php endif; ?>
