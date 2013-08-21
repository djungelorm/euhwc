<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

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
