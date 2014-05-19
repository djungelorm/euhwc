<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 */
?>

		</div><!-- #main -->
		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php get_sidebar( 'main' ); ?>

			<div class="site-info">
        <?php do_action( 'twentythirteen_credits' ); ?>
        <a href="/constitution" target="_blank" class="nowrap">Constitution</a> |
        <a href="/safety-policy" target="_blank" class="nowrap">Safety Policy</a> |
        <a href="/trip-information" target="_blank" class="nowrap">Trip Information</a> |
        <a href="/contact" class="nowrap">Contact Us</a> |
        <a href="http://www.eusu.ed.ac.uk" target="_blank" class="nowrap">EUSU</a>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>