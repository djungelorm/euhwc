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
      $this->upload_logo();
    }
  }

  public function shortcode() {
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
    $num_entries = count(EUHWCLogoCompetition_Logos::get(null, $current_user->ID));
    if ($num_entries >= $max_entries) {
      $out .= '<p>You can\'t submit any more logos. It\'s a maximum of '.$max_entries.' each!</p>';
    } else {
      $num_remaining = $max_entries - $num_entries;
      $out = '<p>';
      if ($num_remaining > 1) {
        $out .= 'You can submit up to ' . $num_remaining . ' more logos.';
      } else {
        $out = 'You can submit 1 more logo.';
      }
      $max_size = EUHWCLogoCompetition_Options::max_upload_size();
      $formats = EUHWCLogoCompetition_Options::upload_valid_formats_human_readable();
      $out .= ' Files must be smaller than '.size_format($max_size).' and be in '.$formats.' format.</p>';
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
    global $current_user;
    if (wp_verify_nonce($_POST['euhwc_logo_competition_upload_form_submitted'], 'euhwc_logo_competition_upload_form')) {
      $result = EUHWCLogoCompetition_Logos::add($current_user, 'euhwc_logo_competition_file');
      if ($result === true) {
        $this->messages[] = '<div class="success">Thank you, your logo has been successfully submitted!</div>';
      } else {
        $this->messages[] = '<div class="error">'.$result.'</div>';
      }
    }
  }

}

$upload = new EUHWCLogoCompetition_Upload;
add_action('init', array($upload, 'process'));
add_shortcode('euhwc_logo_competition_upload', array($upload, 'shortcode'));

?>
