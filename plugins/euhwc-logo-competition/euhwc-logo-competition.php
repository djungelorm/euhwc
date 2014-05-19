<?php
/*
Plugin Name: EUHWC Logo Competition
Description: Provides shortcode to run a logo competition.
Version: 1.0
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
*/

add_action('init', 'euhwc_logo_competition_init');

function euhwc_logo_competition_init() {
  $args = array(
      'labels' => array(
        'name' => __('Logos'),
        'singular_name' => __('Logo'),
        'add_new' => __('Add New Logo'),
        'add_new_item' => __('Add New Logo'),
        'edit_item' => __('Edit Logo'),
        'new_item' => __('Add New Logo'),
        'all_items' => __('View Logos'),
        'view_item' => __('View Logo'),
        'search_items' => __('Search Logos'),
        'not_found' =>  __('No Logos found'),
        'not_found_in_trash' => __('No Logos found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Logos')
      ),
    'public' => true,
    'has_archive' => true,
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'map_meta_cap' => true,
    'menu_position' => null,
    'supports' => array('title', 'editor', 'author', 'thumbnail')
  );

  register_post_type('euhwc_logocomp_entry', $args);
}

define('MAX_UPLOAD_SIZE', 2*1024*1024);
define('TYPE_WHITELIST', serialize(array(
  'image/jpeg',
  'image/png',
  'image/gif'
)));

add_shortcode('euhwc_logo_competition_form', 'euhwc_logo_competition_form_shortcode');
add_shortcode('euhwc_logo_competition_entries', 'euhwc_logo_competition_entries_shortcode');
add_shortcode('euhwc_logo_competition_voting', 'euhwc_logo_competition_voting_shortcode');
add_shortcode('euhwc_logo_competition_results', 'euhwc_logo_competition_results_shortcode');

function euhwc_logo_competition_form_shortcode() {

  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  global $current_user;

  $out = '<h2>Submit a Logo</h2>';

  if (isset($_POST['euhwc_logo_competition_upload_form_submitted']) &&
      wp_verify_nonce($_POST['euhwc_logo_competition_upload_form_submitted'], 'euhwc_logo_competition_upload_form')) {
    $result = euhwc_logo_competition_parse_file($_FILES['euhwc_logo_competition_file']);
    if ($result['error']) {
      $out .= '<div class="error">'.$result['error'].'</div>';
    } else {
      $image_data = array(
        'post_title' => $result['title'],
        'post_status' => 'pending',
        'post_author' => $current_user->ID,
        'post_type' => 'euhwc_logocomp_entry'
      );

      if ($post_id = wp_insert_post($image_data)) {
        euhwc_logo_competition_process_image('euhwc_logo_competition_file', $post_id, $result['title']);
        $out .= '<div class="success">Thank you, your logo has been successfully submitted!</div>';
      }
    }
  }

  $out .= euhwc_logo_competition_get_upload_form();
  return $out;
}

function euhwc_logo_competition_entries_shortcode() {
  global $current_user;

  if (!is_user_logged_in()) {
    return '';
  }

  $out = '<h2>Your Logo Competition Entries</h2>';

  $deleted = false;
  if (isset($_POST['euhwc_logo_competition_form_delete_submitted'] ) &&
      wp_verify_nonce($_POST['euhwc_logo_competition_form_delete_submitted'], 'euhwc_logo_competition_form_delete')) {
    if (isset($_POST['euhwc_logo_competition_image_delete_id'])) {
      if ($num_deleted = euhwc_logo_competition_delete_images($_POST['euhwc_logo_competition_image_delete_id'])) {
        $out .= '<div class="success">The selected entries have been deleted.</div>';
        $deleted = true;
      }
    }
  }

  $table = euhwc_logo_competition_get_table($current_user->ID);
  if ($table) {
    $out .= '<p>Your entries are shown below. Click on one to view it full size.</p>';
    $out .= $table;
  } else {
    if (!$deleted)
      $out .= '<p>You have not yet submitted any logos.</p>';
  }

  return $out;
}


function euhwc_logo_competition_voting_shortcode() {
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  global $current_user;

  $out = '';

  if (isset($_POST['euhwc_logo_competition_form_vote_submitted'] ) &&
      wp_verify_nonce($_POST['euhwc_logo_competition_form_vote_submitted'], 'euhwc_logo_competition_form_vote')) {
    if (isset($_POST['euhwc_logo_competition_image_vote_id'])) {
      $result = euhwc_logo_competition_vote_image($current_user->ID, $_POST['euhwc_logo_competition_image_vote_id']);
      if ($result !== true) {
        $out .= '<div class="error">There was a problem saving your vote. '.$result.'</div>';
      }
    }
  }

  $out .= euhwc_logo_competition_get_voting_table();
  return $out;
}

function euhwc_logo_competition_results_shortcode() {
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  global $current_user;

  return euhwc_logo_competition_get_results_table();
}

function euhwc_logo_competition_get_upload_form() {
  global $current_user;
  if (euhwc_logo_competition_get_num_entries($current_user->ID) >= 5){
    return "<p>You can't submit any more logos. It's a maximum of 5 each!</p>";
  }
  $out = '<form id="euhwc_logo_competition_upload_form" method="post" action="" enctype="multipart/form-data">';
  $out .= wp_nonce_field('euhwc_logo_competition_upload_form', 'euhwc_logo_competition_upload_form_submitted');
  $out .= '<p><input type="file" size="20" name="euhwc_logo_competition_file" id="euhwc_logo_competition_file"> ';
  $out .= '<input type="submit" id="euhwc_logo_competition_submit" name="euhwc_logo_competition_submit" value="Save"></p>';
  $out .= '</form>';
  return $out;
}

function euhwc_logo_competition_get_num_entries($user_id) {
  $args = array(
    'author' => $user_id,
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'pending'
  );

  $user_images = new WP_Query($args);

  return $user_images->post_count;
}

function euhwc_logo_competition_get_table($user_id) {

  $args = array(
    'author' => $user_id,
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'pending'
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

function euhwc_logo_competition_delete_images($images_to_delete) {
  $images_deleted = 0;
  foreach ($images_to_delete as $user_image) {
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

function euhwc_logo_competition_parse_file($file = '') {
  $result = array();
  $result['error'] = 0;
  if ($file['error']) {
    $result['error'] = 'There was an error uploading your file!';
    return $result;
  }

  global $current_user;
  if (euhwc_logo_competition_get_num_entries($current_user->ID) >= 5) {
    $result['error'] = "You've already uploaded 5 logos.";
    return $result;
  }

  $result['title'] = $file['name'];
  $image_data = getimagesize($file['tmp_name']);

  if (!in_array($image_data['mime'], unserialize(TYPE_WHITELIST))) {
    $result['error'] = 'Your logo must be a jpeg, png or gif.';
  } elseif(($file['size'] > MAX_UPLOAD_SIZE)) {
    $result['error'] = 'Your image was too large. It can be at most 2MB.';
  }
  return $result;
}

function euhwc_logo_competition_get_voting_table() {

  global $current_user;

  $args = array(
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'publish'
  );

  $user_images = new WP_Query($args);

  if (!$user_images->post_count)
    return false;

  $out = '<table style="border-bottom: 0px;"><tr>';

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
      $out .= '<input type="submit" name="euhwc_logo_competition_vote" value="Vote for this logo" />';
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

function euhwc_logo_competition_get_results_table() {

  global $current_user;

  $args = array(
    'post_type' => 'euhwc_logocomp_entry',
    'post_status' => 'publish'
  );

  $user_images = new WP_Query($args);

  if (!$user_images->post_count)
    return false;

  $out = '<table style="border-bottom: 0px;"><tr>';

  $i = 0;

  foreach ($user_images->posts as $user_image) {
    $votes = get_post_meta($user_image->ID, 'logo_competition_vote', false);

    $out .= '<td style="border-top: 0px;">';
    $post_thumbnail_id = get_post_thumbnail_id($user_image->ID);
    $out .= wp_get_attachment_link($post_thumbnail_id, 'thumbnail');
    $out .= '<br/>';
    $out .= count($votes) . ' votes';
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
