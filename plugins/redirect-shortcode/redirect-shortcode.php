<?php
/*
Plugin Name: Redirect Shortcode
Description: Provides a redirect shortcode that redirects to a specified URL.
Version: 1.0
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

add_shortcode('redirect', 'do_redirect');

function do_redirect($atts) {
  if (isset($atts['url']) && !empty($atts['url'])) {
    $url = esc_url($atts['url']);
    wp_redirect($url);
  } else {
    wp_redirect('/404.php');
  }
  exit;
}

?>
