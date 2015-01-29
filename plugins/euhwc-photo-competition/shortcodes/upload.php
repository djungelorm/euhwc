<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Functionality for uploading logos to the logo competition */
class EUHWCPhotoCompetition_Upload {

  private $messages = array();

  /** Process an upload form submission */
  public function process() {
    global $current_user;
    $categories = EUHWCPhotoCompetition_Categories::get();
    $max_entries = EUHWCPhotoCompetition_Options::max_entries_per_category();

    // Process photo submission form
    if (isset($_POST['euhwc_photo_competition_upload_form_submitted']) &&
        wp_verify_nonce($_POST['euhwc_photo_competition_upload_form_submitted'], 'euhwc_photo_competition_upload_form')) {
      foreach ($categories as $category) {
        // Look for a new photo
        $key = 'euhwc_photo_competition_file_'.$category->term->term_id;
        if (array_key_exists($key, $_FILES) && $_FILES[$key]['name']) {
          $result = EUHWCPhotoCompetition_Photos::add($category, $current_user, $key);
          if ($result !== TRUE) {
            array_push($this->messages, '<div class="fail">Failed to upload photo. '.$result.'</div>');
          } else {
            array_push($this->messages, '<div class="success">Your photo has been successfully submitted.</div>');
          }
        }
      }
    }

    // Process photo deletions
    foreach ($categories as $category) {
      $photos = EUHWCPhotoCompetition_Photos::get($category);
      foreach ($photos as $photo) {
        $key = 'euhwc_photo_competition_delete_'.$category->term->term_id.'_'.$photo->post->ID;
        if (array_key_exists($key, $_POST) &&
            wp_verify_nonce($_POST['euhwc_photo_competition_upload_form_submitted'], 'euhwc_photo_competition_upload_form')) {
          $result = $photo->delete();
          array_push($this->messages, '<div class="success">The photo has been successfully deleted.</div>');
        }
      }
    }
  }

  public function shortcode() {
    // Require the user to be logged in
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }

    return $this->generate_form();
  }

  private function generate_form() {
    global $current_user;
    $out = '';

    // Display error messages
    foreach ($this->messages as $message) {
      $out .= $message;
    }

    $max_entries = EUHWCPhotoCompetition_Options::max_entries_per_category();
    $categories = EUHWCPhotoCompetition_Categories::get();
    $photos_per_row = 3;

    $out .= '<form id="euhwc_photo_competition_upload_form" method="post" action="" enctype="multipart/form-data">';
    $out .= wp_nonce_field('euhwc_photo_competition_upload_form', 'euhwc_photo_competition_upload_form_submitted');

    // Output input fields for each category
    foreach ($categories as $category) {

      $out .= '<h2 style="display: inline; margin-right: 1em;">' . $category->term->name . '</h2>';
      $out .= '<p>'.$category->term->description.'</p>';

      // Get submitted photos, and pad with false up to max_entries
      $photos = EUHWCPhotoCompetition_Photos::get($category);
      $num_photos_submitted = count($photos);
      for ($i = count($photos); $i < $max_entries; $i++) {
        array_push($photos, false);
      }

      // Output submission rows
      $out .= '<table style="border-bottom: 0px;">';
      for ($row = 0; $row < ceil(count($photos) / $photos_per_row); $row++) {
        $photos_row = array_slice($photos, $row*$photos_per_row, $photos_per_row);

        // Output photos/placeholders
        $out .= '<tr>';
        foreach ($photos_row as $photo) {
          if ($photo) {
            $out .= '<td align="center" style="border: 0px;">';
            $out .= $photo->get_attachment_link();
            $out .= '</td>';
          } else {
            $out .= '<td align="center" style="border: 0px;">';
            $out .= '<img src="' . plugins_url('images/placeholder.png', dirname(__FILE__)) . '" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;"/>';
            $out .= '</td>';
          }
        }

        // Output upload/delete buttons
        $out .= '</tr><tr>';
        foreach ($photos_row as $photo) {
          if ($photo) {
            $out .= '<td align="center" style="border: 0px;">';
            $out .= '<input type="submit" name="euhwc_photo_competition_delete_'.$category->term->term_id.'_'.$photo->post->ID.'" value="Delete" onclick="if(confirm(\'Are you sure you want to delete this photo?\')) return true; return false;"/>';
            $out .= '</td>';
          } else {
            //$out .= '<td align="center" style="border: 0px;">';
            //$out .= '<input type="file" name="euhwc_photo_competition_file_'.$category->term->term_id.'_'.$i.'" id="euhwc_photo_competition_file_'.$category->term->term_id.'_'.$i.'"/>';
            //$out .= '</td>';
            $out .= '<td></td>';
          }
        }

      }

      if ($num_photos_submitted < $max_entries) {
        $out .= '<tr>';
        $out .= '<td colspan="'.$photos_per_row.'" align="left" style="border: 0px;">';
        $out .= '<input type="file" name="euhwc_photo_competition_file_'.$category->term->term_id.'" id="euhwc_photo_competition_file_'.$category->term->term_id.'"/>';
        $out .= '<input type="submit" id="euhwc_photo_competition_submit" name="euhwc_photo_competition_submit" value="Save"/>';
        $out .= '</td>';
        $out .= '</tr>';
      }
      $out .= '</table>';
    }
    $out .= '</form>';
    return $out;
  }

}

$upload = new EUHWCPhotoCompetition_Upload;
add_action('the_post', array($upload, 'process'));
add_shortcode('euhwc_photo_competition_upload', array($upload, 'shortcode'));

?>
