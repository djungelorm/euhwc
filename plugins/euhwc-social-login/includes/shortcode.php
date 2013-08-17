<?php

/**
 * Remove OneAll shortcodes
 */

function euhwc_social_login_remove_shortcode() {
  remove_shortcode ('oa_social_login');
}

add_action('plugins_loaded', 'euhwc_social_login_remove_shortcode');

/**
 * Add customised shortcode
 */

function euhwc_social_login_shortcode_handler ($args)
{
	if ( ! is_user_logged_in ())
	{
		return euhwc_social_login_render_login_form ('shortcode');
	}
	return '';
}

add_shortcode ('euhwc_social_login', 'euhwc_social_login_shortcode_handler');

?>
