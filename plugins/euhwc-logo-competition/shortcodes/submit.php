<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Add [euhwc_logo_competition_submit] shortcode
 */

add_shortcode('euhwc_logo_competition_submit', 'euhwc_logo_competition_submit_shortcode');

function euhwc_logo_competition_submit_shortcode($atts, $content = null) {
  global $current_user;
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  // Parse attributes
  $max_entries = 5;
  foreach ($atts as $key => $att) {
    if ($key == 'max_entries')
      $max_entries = $att;
  }

  $out = '<h2>Submit a Logo</h2>';
  if (isset($_POST['euhwc_logo_competition_submit_form_submitted'])) {
    $out .= euhwc_logo_competition_submit_logo($max_entries);
  }
  $out .= euhwc_logo_competition_get_submit_form($max_entries);
  return $out;
}

/** Generate upload form HTML */
function euhwc_logo_competition_get_submit_form($max_entries) {
  global $current_user;
  $out = '';
  $num_entries = euhwc_logo_competition_get_num_entries($current_user->ID);
  if ($num_entries >= $max_entries){
    $out .= '<p>You can\'t submit any more logos. It\'s a maximum of '.$max_entries.' each!</p>';
  } else {
    $out = '<p>Entries remaining: '.($max_entries-$num_entries).'</p>';
    $out .= '<form id="euhwc_logo_competition_submit_form" method="post" action="" enctype="multipart/form-data">';
    $out .= wp_nonce_field('euhwc_logo_competition_submit_form', 'euhwc_logo_competition_submit_form_submitted');
    $out .= '<p><input type="file" size="20" name="euhwc_logo_competition_file" id="euhwc_logo_competition_file"> ';
    $out .= '<input type="submit" id="euhwc_logo_competition_submit" name="euhwc_logo_competition_submit" value="Save"></p>';
    $out .= '</form>';
  }
  return $out;
}

/** Process an upload form submission */
function euhwc_logo_competition_submit_logo($max_entries) {
  $out = '';
  if (wp_verify_nonce($_POST['euhwc_logo_competition_submit_form_submitted'], 'euhwc_logo_competition_submit_form')) {
    $result = euhwc_logo_competition_parse_file($_FILES['euhwc_logo_competition_file'], $max_entries);
    if ($result['error']) {
      $out .= '<div class="error">'.$result['error'].'</div>';
    } else {
      $image_data = array(
        'post_title' => $current_user->display_name.' ('.$result['title'].')',
        'post_status' => 'publish',
        'post_author' => $current_user->ID,
        'post_type' => 'euhwc_logocomp_entry'
      );

      if ($post_id = wp_insert_post($image_data)) {
        euhwc_logo_competition_process_image('euhwc_logo_competition_file', $post_id, $result['title']);
        $out .= '<div class="success">Thank you, your logo has been successfully submitted!</div>';
      }
    }
  }
  return $out;
}

/** Parse a file form submission returning a result array summarising the submission */
function euhwc_logo_competition_parse_file($file, $max_entries) {
  $result = array();
  $result['error'] = false;

  // Check for upload errors
  if ($file['error']) {
    $result['error'] = 'There was an error uploading your file!';
    return $result;
  }

  // Check the user hasn't exceeded the entries limit
  global $current_user;
  if (euhwc_logo_competition_get_num_entries($current_user->ID) >= $max_entries) {
    $result['error'] = 'You\'ve already uploaded '.$max_entries.' logos.';
    return $result;
  }

  $result['title'] = $file['name'];
  $image_data = getimagesize($file['tmp_name']);

  // Check the type and size of the image
  $max_upload_size = 2*1024*1024;
  $type_whitelist = serialize(array(
    'image/jpeg',
    'image/png',
    'image/gif'
  ));
  if (!in_array($image_data['mime'], unserialize($type_whitelist))) {
    $result['error'] = 'Your logo must be a jpeg, png or gif.';
  } elseif(($file['size'] > $max_upload_size)) {
    $result['error'] = 'Your image was too large. It can be at most 2MB.';
  }
  return $result;
}

/** Attach a logo to a post */
function euhwc_logo_competition_process_image($file, $post_id) {
  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

  $attachment_id = media_handle_upload($file, $post_id);
  update_post_meta($post_id, '_thumbnail_id', $attachment_id);
  $attachment_data = array(
    'ID' => $attachment_id,
    'post_excerpt' => ''
  );
  wp_update_post($attachment_data);
  return $attachment_id;
}

?>
