<?php
/*
Plugin Name: EUHWC Social Login
Description: Customizes OneAll social login links to show an EUHWC button that can be used to log in using the website.
Version: 1.0
Author: Alex Collins
*/

/**
 * Render EUHWC login button
 */
function euhwc_social_login_button() {
  wp_enqueue_style('euhwc-social-login-button');
  $content[] = '<div class="oneall_euhwc_link">';
  $content[] = '<a href="/login" title="Log in using the EUHWC website">';
  $content[] = '<img src="' . plugins_url('euhwc-social-login/images/euhwc-button.png', 'euhwc-social-login') . '" rel="nofollow" alt="EUHWC" />';
  $content[] = '</a>';
  $content[] = '</div>';
  return implode($content, "\n");
}

/**
 * Render login form
 */
function euhwc_social_login_render_login_form() {
  $oa_form = oa_social_login_render_login_form ('widget', $instance);
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
wp_register_style('euhwc-social-login-button', plugins_url('euhwc-social-login/css/button.css', 'euhwc-social-login'));

?>
