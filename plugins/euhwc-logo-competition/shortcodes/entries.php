<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Add [euhwc_logo_competition_entries] shortcode
 */

add_shortcode('euhwc_logo_competition_entries', 'euhwc_logo_competition_entries_shortcode');

function euhwc_logo_competition_entries_shortcode($atts, $content = null) {
  global $current_user;
  if (!is_user_logged_in()) {
    return '';
  }

  // Parse attributes
  $year = date('Y');
  foreach ($atts as $key => $att) {
    if ($key == 'year')
      $year = $att;
  }

  $out = '<h2>Your Logo Competition Entries</h2>';

  $deleted = false;
  if (isset($_POST['euhwc_logo_competition_form_delete_submitted'])) {
    $deleted = euhwc_logo_competition_delete_logo();
  }

  $table = euhwc_logo_competition_get_table($current_user->ID, $year);
  if ($table) {
    $out .= '<p>Your entries are shown below. Click on one to view it full size.</p>';
    $out .= $table;
  } else {
    if (!$deleted)
      $out .= '<p>You have not yet submitted any logos.</p>';
  }

  return $out;
}

/** Process a delete request, returns true if a logo is successfully deleted */
function euhwc_logo_competition_delete_logo() {
  $deleted = false;
  if (wp_verify_nonce($_POST['euhwc_logo_competition_form_delete_submitted'], 'euhwc_logo_competition_form_delete')) {
    if (isset($_POST['euhwc_logo_competition_image_delete_id'])) {
      if (euhwc_logo_competition_delete_logos($_POST['euhwc_logo_competition_image_delete_id']) > 0) {
        $out .= '<div class="success">The selected entries have been deleted.</div>';
        $deleted = true;
      }
    }
  }
  return $deleted;
}

/** Generate HTML for a table summarising the users entries */
function euhwc_logo_competition_get_table($user_id, $year) {
  $args = array(
    'author' => $user_id,
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'publish',
    'year' => $year
  );
  $user_images = new WP_Query($args);

  if (!$user_images->post_count)
    return false;

  $out = '<form method="post" action="">';
  $out .= wp_nonce_field('euhwc_logo_competition_form_delete', 'euhwc_logo_competition_form_delete_submitted');

  $out .= '<table style="border-bottom: 0px;"><tr>';

  $i = 0;

  foreach ($user_images->posts as $user_image) {
    $out .= '<td style="border-top: 0px;">';
    $post_thumbnail_id = get_post_thumbnail_id($user_image->ID);
    $out .= wp_nonce_field('euhwc_logo_competition_image_delete_' . $user_image->ID, 'euhwc_logo_competition_image_delete_id_' . $user_image->ID, false);
    $out .= wp_get_attachment_link($post_thumbnail_id, 'thumbnail');
    $out .= '<br/>';
    $out .= '<input type="checkbox" name="euhwc_logo_competition_image_delete_id[]" value="' . $user_image->ID . '" /> Mark for deletion';
    $out .= '</td>';
    $i++;
    if ($i % 3 == 0)
      $out .= '</tr><tr>';
  }
  $out .= '</tr>';

  $out .= '<tr><td style="border-top: 0px;" colspan="3"><input type="submit" name="euhwc_logo_competition_delete" value="Delete selected entries" /></td></tr>';

  $out .= '</table>';
  $out .= '</form>';

  return $out;
}

/** Delete the given logos */
function euhwc_logo_competition_delete_logos($logos) {
  $logos_deleted = 0;
  foreach ($logos as $user_image) {
    if (isset($_POST['euhwc_logo_competition_image_delete_id_' . $user_image]) &&
       wp_verify_nonce($_POST['euhwc_logo_competition_image_delete_id_' . $user_image], 'euhwc_logo_competition_image_delete_' . $user_image)) {
      if ($post_thumbnail_id = get_post_thumbnail_id($user_image)) {
        wp_delete_attachment($post_thumbnail_id);
      }
      wp_trash_post($user_image);
      $images_deleted++;
    }
  }
  return $images_deleted;
}

?>
