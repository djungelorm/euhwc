<?php
/*
Plugin Name: Redirect Shortcode
Description: Provides a redirect shortcode that redirects to a specified URL.
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

add_action('template_redirect', 'redirect_shortcode_preprocess', 1);
add_shortcode('redirect', 'redirect_shortcode_do_redirect');

function redirect_shortcode_preprocess() {
  if (!is_singular())
    return;
  global $post;
  if (!empty($post->post_content) && strpos($post->post_content, '[redirect') !== false) {
    if (preg_match("/\[redirect url='[^']+'\]/", $post->post_content))
      do_shortcode($post->post_content);
  }
}

function redirect_shortcode_do_redirect($atts) {
  if (isset($atts['url']) && !empty($atts['url'])) {
    $url = esc_url($atts['url']);
    if (headers_sent())
      return 'Failed to redirect to <a href="'.$url.'">'.$url.'</a>';
    wp_redirect($url);
  } else {
    if (headers_sent())
      return 'Failed to redirect';
    wp_redirect('/404.php');
  }
  exit;
}

?>
