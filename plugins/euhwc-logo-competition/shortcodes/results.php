<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Add [euhwc_logo_competition_results] shortcode
 */

add_shortcode('euhwc_logo_competition_results', 'euhwc_logo_competition_results_shortcode');

function euhwc_logo_competition_results_shortcode($atts, $content = null) {
  global $current_user;
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  // Parse attributes
  $year = date('Y');
  foreach ($atts as $key => $att) {
    if ($key == 'year')
      $year = $att;
  }

  return euhwc_logo_competition_get_results_table($year);
}

/** Generate results table */
function euhwc_logo_competition_get_results_table($year) {

  global $current_user;

  $args = array(
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'publish',
    'year' => $year
  );

  $user_images = new WP_Query($args);

  if (!$user_images->post_count)
    return false;

  $out = '<table style="border-bottom: 0px;"><tr>';

  $i = 0;

  $logos = $user_images->posts;
  usort($logos, 'euhwc_logo_competition_results_cmp');

  foreach ($logos as $logo) {
    $num_votes = euhwc_logo_competition_num_votes($logo);
    $out .= '<td style="border-top: 0px;">';
    $post_thumbnail_id = get_post_thumbnail_id($logo->ID);
    $out .= wp_get_attachment_link($post_thumbnail_id, 'thumbnail');
    $out .= '<br/>';
    $out .= get_the_author_meta('display_name', $logo->post_author) . ', '. $num_votes . ' votes';
    $out .= '</td>';
    $i++;
    if ($i % 3 == 0)
      $out .= '</tr><tr>';
  }
  $out .= '</tr>';

  $out .= '</table>';
  $out .= '</form>';

  return $out;
}

?>
