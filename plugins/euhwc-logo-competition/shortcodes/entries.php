<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Show the logos that a user has submitted */
class EUHWCLogoCompetition_Entries {

  private $messages = array();

  public function process() {
    if (isset($_POST['euhwc_logo_competition_form_delete_submitted'])) {
      $this->delete_logo();
    }
  }

  public function shortcode($atts) {
    global $current_user;
    if (!is_user_logged_in()) {
      return '';
    }

    $atts = shortcode_atts(array('year' => date('Y')), $atts);

    $out = '<h2>Your Logo Competition Entries</h2>';
    foreach ($this->messages as $message) {
      $out .= $message;
    }
    $num_entries = count(EUHWCLogoCompetition_Logos::get($atts['year'], $current_user->ID));
    if ($num_entries > 0) {
      $out .= '<p>Your entries are shown below. Click on one to view it full size.</p>';
      $out .= $this->generate_table($current_user->ID, $atts['year']);
    } else {
      $out .= '<p>You have not submitted any logos.</p>';
    }

    return $out;
  }

  /** Process a delete request, returns true if a logo is successfully deleted */
  private function delete_logo() {
    if (wp_verify_nonce($_POST['euhwc_logo_competition_form_delete_submitted'], 'euhwc_logo_competition_form_delete')) {
      if (isset($_POST['euhwc_logo_competition_image_delete_id'])) {
        $ids = $_POST['euhwc_logo_competition_image_delete_id'];
        foreach ($ids as $id) {
          if (isset($_POST['euhwc_logo_competition_image_delete_id_' . $id]) &&
            wp_verify_nonce($_POST['euhwc_logo_competition_image_delete_id_'.$id], 'euhwc_logo_competition_image_delete_'.$id)) {
            $logo = EUHWCLogoCompetition_Logos::get_logo_by_id($id);
            $logo->delete();
          }
        }
        $this->messages[] = '<div class="success">The selected logos have been deleted.</div>';
      }
    }
  }

  /** Generate HTML for a table summarising the users entries */
  private function generate_table($user_id, $year) {
    $logos = EUHWCLogoCompetition_Logos::get($year, $user_id);
    if (count($logos) == 0)
      return '';

    $out = '<form method="post" action="">';
    $out .= wp_nonce_field('euhwc_logo_competition_form_delete', 'euhwc_logo_competition_form_delete_submitted');

    $out .= '<table style="border-bottom: 0px;"><tr>';

    $i = 0;

    foreach ($logos as $logo) {
      $out .= '<td style="border-top: 0px;">';
      $out .= wp_nonce_field('euhwc_logo_competition_image_delete_' . $logo->post->ID, 'euhwc_logo_competition_image_delete_id_' . $logo->post->ID, false);
      $out .= $logo->get_attachment_link();
      $out .= '<br/>';
      $out .= '<input type="checkbox" name="euhwc_logo_competition_image_delete_id[]" value="' . $logo->post->ID . '" id="euhwc_logo_competition_image_delete_'.$logo->post->ID.'"/> <label for="euhwc_logo_competition_image_delete_'.$logo->post->ID.'">Mark for deletion</label>';
      $out .= '</td>';
      $i++;
      if ($i % 3 == 0)
        $out .= '</tr><tr>';
    }
    $out .= '</tr>';

    $out .= '<tr><td style="border-top: 0px;" colspan="3"><input type="submit" name="euhwc_logo_competition_delete" value="Delete selected logos" /></td></tr>';

    $out .= '</table>';
    $out .= '</form>';

    return $out;
  }

}

$entries = new EUHWCLogoCompetition_Entries;
add_action('the_post', array($entries, 'process'));
add_shortcode('euhwc_logo_competition_entries', array($entries, 'shortcode'));

?>
