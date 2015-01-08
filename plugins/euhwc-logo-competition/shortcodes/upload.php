<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Functionality for uploading logos to the logo competition */
class EUHWCLogoCompetition_Upload {

  private $messages = array();

  /** Process an upload form submission */
  public function process() {
    if (is_user_logged_in() && isset($_POST['euhwc_logo_competition_upload_form_submitted'])) {
      $this->messages[] = $this->upload_logo();
    }
  }

  public function shortcode() {
    global $current_user;
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }

    return '<h2>Submit a Logo</h2>' . $this->generate_form();
  }

  /** Generate upload form HTML */
  private function generate_form() {
    global $current_user;
    $max_entries = EUHWCLogoCompetition_Options::max_entries();
    $out = '';
    $num_entries = euhwc_logo_competition_get_num_entries($current_user->ID);
    if ($num_entries >= $max_entries) {
      $out .= '<p>You can\'t submit any more logos. It\'s a maximum of '.$max_entries.' each!</p>';
    } else {
      $out = '<p>You can submit up to '.($max_entries-$num_entries).' more logos</p>';
    }
    foreach ($this->messages as $message) {
      $out .= $message;
    }
    if ($num_entries < $max_entries) {
      $out .= '<form id="euhwc_logo_competition_upload_form" method="post" action="" enctype="multipart/form-data">';
      $out .= wp_nonce_field('euhwc_logo_competition_upload_form', 'euhwc_logo_competition_upload_form_submitted');
      $out .= '<p><input type="file" size="20" name="euhwc_logo_competition_file" id="euhwc_logo_competition_file"> ';
      $out .= '<input type="submit" id="euhwc_logo_competition_upload" name="euhwc_logo_competition_upload" value="Save"></p>';

      $out .= '</form>';
    }
    return $out;
  }

  /** Process an upload form submission */
  private function upload_logo() {
    $out = '';
    if (wp_verify_nonce($_POST['euhwc_logo_competition_upload_form_submitted'], 'euhwc_logo_competition_upload_form')) {
      $result = $this->parse_file($_FILES['euhwc_logo_competition_file']);
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
          $this->process_image('euhwc_logo_competition_file', $post_id, $result['title']);
          $out .= '<div class="success">Thank you, your logo has been successfully submitted!</div>';
        }
      }
    }
    return $out;
  }

  /** Parse a file form submission returning a result array summarising the submission */
  private function parse_file($file) {
    $result = array();
    $result['error'] = false;

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
      if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        $result['error'] = 'Your image was too large.';
      } else if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $result['error'] = 'Please choose a file and try again.';
      } else {
        $result['error'] = 'There was an error uploading your file!';
      }
      return $result;
    }

    // Check the user hasn't exceeded the entries limit
    global $current_user;
    $max_entries = EUHWCLogoCompetition_Options::max_entries();
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
  private function process_image($file, $post_id) {
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

}

$upload = new EUHWCLogoCompetition_Upload;
add_action('init', array($upload, 'process'));
add_shortcode('euhwc_logo_competition_upload', array($upload, 'shortcode'));

?>
