<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Add [euhwc_logo_competition_voting] shortcode
 */

add_shortcode('euhwc_logo_competition_voting', 'euhwc_logo_competition_voting_shortcode');

function euhwc_logo_competition_voting_shortcode($atts, $content = null) {
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

  $out = '';
  if (isset($_POST['euhwc_logo_competition_form_vote_submitted']) &&
      wp_verify_nonce($_POST['euhwc_logo_competition_form_vote_submitted'], 'euhwc_logo_competition_form_vote')) {
    if (isset($_POST['euhwc_logo_competition_image_vote_id'])) {
      $result = euhwc_logo_competition_vote_image($current_user->ID, $_POST['euhwc_logo_competition_image_vote_id']);
      if ($result !== true) {
        $out .= '<div class="error">There was a problem saving your vote. '.$result.'</div>';
      }
    }
  }

  $out .= euhwc_logo_competition_get_voting_table($year);
  return $out;
}

/** Generate table for casting votes */
function euhwc_logo_competition_get_voting_table($year) {
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

  // Check if the user has voted
  $has_voted = false;
  foreach ($user_images->posts as $user_image) {
    $vote = in_array($current_user->ID, get_post_meta($user_image->ID, 'logo_competition_vote', false));
    if ($vote) {
      $has_voted = true;
      break;
    }
  }

  $i = 0;

  foreach ($user_images->posts as $user_image) {
    $vote = in_array($current_user->ID, get_post_meta($user_image->ID, 'logo_competition_vote', false));

    $out .= '<td style="border-top: 0px;">';
    if ($vote) {
      $out .= '<div class="success">';
    } else {
      $out .= '<form method="post" action="">';
      $out .= wp_nonce_field('euhwc_logo_competition_form_vote', 'euhwc_logo_competition_form_vote_submitted');
    }
    $post_thumbnail_id = get_post_thumbnail_id($user_image->ID);
    $out .= wp_nonce_field('euhwc_logo_competition_image_vote_' . $user_image->ID, 'euhwc_logo_competition_image_vote_id_' . $user_image->ID, false);
    $out .= wp_get_attachment_link($post_thumbnail_id, 'thumbnail');
    $out .= '<br/>';
    if ($vote) {
      $out .= 'You have voted for this logo.</div>';
    } else {
      $out .= '<input type="hidden" name="euhwc_logo_competition_image_vote_id" value="' . $user_image->ID . '" />';
      if ($has_voted) {
        $out .= '<input type="submit" name="euhwc_logo_competition_vote" value="Vote for this logo instead" />';
      } else {
        $out .= '<input type="submit" name="euhwc_logo_competition_vote" value="Vote for this logo" />';
      }
      $out .= '</form>';
    }
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

/** Add a vote to the given logo */
function euhwc_logo_competition_vote_image($user_id, $image_id) {
  if (!isset($_POST['euhwc_logo_competition_image_vote_id_' . $image_id])) {
    return 'Image ID not selected';
  }
  if (wp_verify_nonce($_POST['euhwc_logo_competition_image_vote_id_' . $image_id], 'euhwc_logo_competition_image_vote_' . $image_id)) {
    // clear all the users votes
    $args = array(
      'post_type' => 'euhwc_logocomp_entry',
      'post_status' => 'publish'
    );
    $images = new WP_Query($args);
    foreach ($images->posts as $image) {
      delete_post_meta($image->ID, 'logo_competition_vote', $user_id);
    }
    // add the new vote
    $result = add_post_meta($image_id, 'logo_competition_vote', $user_id);
    if (!$result) {
      return 'Failed to save your vote';
    }
    return true;
  }
  return 'Nonce not valid';
}

?>
