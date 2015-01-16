<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Show the logo competition results */
class EUHWCLogoCompetition_Results {

  public function shortcode($atts) {
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }
    $atts = shortcode_atts(array('year' => date('Y')), $atts);
    return $this->generate_table($atts['year']);
  }

  /** Generate results table */
  private function generate_table($year) {
    $logos = EUHWCLogoCompetition_Logos::get($year, null, true);
    if (count($logos) == 0) {
      return '<div class="fail">No logos were submitted to the competition - so there are no results!</div>';
    }

    $out = '<table style="border-bottom: 0px;"><tr>';

    $i = 0;
    foreach ($logos as $logo) {
      $out .= '<td style="border-top: 0px;">';
      $out .= $logo->get_attachment_link();
      $out .= '<br/>';
      $out .= $logo->get_author_display_name() . ', ';
      $num_votes = $logo->get_num_votes();
      $out .= $num_votes . ' vote' . ($num_votes == 1 ? '' : 's');
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

}

$results = new EUHWCLogoCompetition_Results;
add_shortcode('euhwc_logo_competition_results', array($results, 'shortcode'));

?>
