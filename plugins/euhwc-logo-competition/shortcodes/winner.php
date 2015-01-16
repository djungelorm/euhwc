<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Functionality for showing the logo competition winner */
class EUHWCLogoCompetition_Winner {

  public function shortcode($atts) {
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }
    $atts = shortcode_atts(array('year' => date('Y')), $atts);
    return $this->generate_winner($atts['year']);
  }

  /** Generate results table */
  private function generate_winner($year) {
    $logos = EUHWCLogoCompetition_Logos::get($year, null, true);
    if (count($logos) == 0) {
      return '<div class="failure">No logos were submitted to the competition - so there is no winner!</div>';
    }
    $winner = $logos[0];
    $out = '<p>';
    $out .= $winner->get_attachment_link();
    $out .= '<br/>';
    $out .= 'By '.$winner->get_author_display_name();
    $out .= '</p>';
    return $out;
  }

}

$winner = new EUHWCLogoCompetition_Winner;
add_shortcode('euhwc_logo_competition_winner', array($winner, 'shortcode'));

?>
