<?php
/*
Plugin Name: EUHWC Social Login
Description: Customizes OneAll social login links to show an EUHWC button that can be used to log in using the website.
Version: 1.1
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
*/
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

/**
 * Render EUHWC login button
 */
function euhwc_social_login_button() {
  wp_enqueue_style('euhwc-social-login-button');
  $content[] = '<div class="oneall_euhwc_link">';
  $content[] = '<a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '" title="Log in using the EUHWC website">';
  $content[] = '<img src="' . plugins_url('euhwc-social-login/images/euhwc-button.png', 'euhwc-social-login') . '" rel="nofollow" alt="EUHWC" />';
  $content[] = '</a>';
  $content[] = '</div>';
  return implode($content, "\n");
}

/**
 * Render login form
 */
function euhwc_social_login_render_login_form($type, $instance) {
  $oa_form = oa_social_login_render_login_form ($type, $instance);
  $button = euhwc_social_login_button();
  $div = '<div class="oneall_social_login">';
  return str_replace($div, $div . $button, $oa_form);
}

/**
 * Override OneAll functionality
 */
require_once(dirname (__FILE__) . '/includes/widget.php');
require_once(dirname (__FILE__) . '/includes/shortcode.php');
require_once(dirname (__FILE__) . '/includes/comment-form.php');

/**
 * Register stylesheet
 */
function euhwc_social_login_scripts() {
  wp_register_style('euhwc-social-login-button', plugins_url('euhwc-social-login/css/button.css', 'euhwc-social-login'));
}
add_action('wp_enqueue_scripts', 'euhwc_social_login_scripts');

?>
