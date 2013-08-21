<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

/**
 * Remove OneAll comment form filter
 */
function euhwc_social_login_remove_comment_form_defaults()
{
  remove_filter ('comment_form_defaults', 'oa_social_login_filter_comment_form_defaults');
}

add_action('plugins_loaded', 'euhwc_social_login_remove_comment_form_defaults');

/**
 * Customised comment form filter
 */
function euhwc_social_login_filter_comment_form_defaults ($default_fields)
{
	//No need to go further if comments disabled or user loggedin
	if (is_array ($default_fields) AND comments_open () AND !is_user_logged_in ())
	{
		//Read settings
		$settings = get_option ('oa_social_login_settings');

		//Display buttons if option not set or disabled
		if (!empty ($settings ['plugin_comment_show_if_members_only']))
		{
			if (!isset ($default_fields ['must_log_in']))
			{
				$default_fields ['must_log_in'] = '';
			}
			$default_fields ['must_log_in'] .= euhwc_social_login_render_login_form ('comments');
		}
	}
	return $default_fields;
}

add_filter ('comment_form_defaults', 'euhwc_social_login_filter_comment_form_defaults');

?>
