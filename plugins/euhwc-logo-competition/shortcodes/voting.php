<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Allow users to vote for a logo */
class EUHWCLogoCompetition_Voting {

  public function process() {
    if (isset($_POST['euhwc_logo_competition_form_vote_submitted'])) {
      $this->vote();
    }
  }

  public function shortcode($atts) {
    global $current_user;
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }

    $atts = shortcode_atts(array('year' => date('Y')), $atts);
    return $this->generate_table($atts['year']);
  }

  /** Generate table for casting votes */
  private function generate_table($year) {
    global $current_user;

    $logos = EUHWCLogoCompetition_Logos::get($year);
    if (count($logos) == 0)
      return '';

    $out = '<table style="border-bottom: 0px;"><tr>';

    // Determine if the user has voted for a logo
    $has_voted = false;
    foreach ($logos as $logo) {
      $has_voted = $logo->has_users_vote($current_user->ID);
      if ($has_voted)
        break;
    }

    $i = 0;

    foreach ($logos as $logo) {
      $vote = $logo->has_users_vote($current_user->ID);

      $out .= '<td style="border-top: 0px;">';
      if ($vote) {
        $out .= '<div class="success">';
      } else {
        $out .= '<form method="post" action="">';
        $out .= wp_nonce_field('euhwc_logo_competition_form_vote', 'euhwc_logo_competition_form_vote_submitted');
      }
      $out .= wp_nonce_field('euhwc_logo_competition_image_vote_' . $logo->post->ID, 'euhwc_logo_competition_image_vote_id_' . $logo->post->ID, false);
      $out .= '<input type="hidden" name="euhwc_logo_competition_year" value="'.$year.'">';
      $out .= $logo->get_attachment_link();
      $out .= '<br/>';
      if ($vote) {
        $out .= 'You have voted for this logo</div>';
      } else {
        $out .= '<input type="hidden" name="euhwc_logo_competition_image_vote_id" value="' . $logo->post->ID . '" />';
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

  /** Add a vote for the given user to the given logo */
  private function vote() {
    global $current_user;
    if (wp_verify_nonce($_POST['euhwc_logo_competition_form_vote_submitted'], 'euhwc_logo_competition_form_vote')) {
      if (isset($_POST['euhwc_logo_competition_image_vote_id'])) {
        $logo_id = $_POST['euhwc_logo_competition_image_vote_id'];
        if (wp_verify_nonce($_POST['euhwc_logo_competition_image_vote_id_' . $logo_id], 'euhwc_logo_competition_image_vote_' . $logo_id)) {
          $year = $_POST['euhwc_logo_competition_year'];
          $logo = EUHWCLogoCompetition_Logos::get_logo_by_id($logo_id);
          $result = $logo->vote_for($current_user->ID, $year);
          if ($result) {
            $messages[] = '<div class="error">Failed to save your vote.</div>';
          }
        }
      }
    }
  }

}

$voting = new EUHWCLogoCompetition_Voting;
add_action('the_post', array($voting, 'process'));
add_shortcode('euhwc_logo_competition_voting', array($voting, 'shortcode'));

?>
