<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
?>

		</div><!-- #main -->
		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php get_sidebar( 'main' ); ?>

			<div class="site-info">
        <?php do_action( 'twentythirteen_credits' ); ?>
        <a href="/constitution" target="_blank">Constitution</a> |
        <a href="/safety-policy" target="_blank">Safety Policy</a> |
        <a href="/contact">Contact Us</a> |
        <a href="http://www.eusu.ed.ac.uk" target="_blank">EUSU</a>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>