<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Show the photo competition results */
class EUHWCPhotoCompetition_Results {

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
    $out = '';
    $categories = EUHWCPhotoCompetition_Categories::get();
    foreach ($categories as $category) {

      $out .= '<h2 style="display: inline; margin-right: 1em;">' . $category->term->name . '</h2>';
      $out .= $category->term->description;

      $photos = EUHWCPhotoCompetition_Photos::get($category, $year, null, true);
      if (count($photos) == 0) {
        $out .= '<div class="fail">No logos were submitted to this category - so there are no results!</div>';
      } else {
        $photos = array_slice($photos, 0, 3);
        $names = array('Winner', 'Second', 'Third');
        $out .= '<table><tr>';
        for ($i = 0; $i < count($photos); $i++) {
          $photo = $photos[$i];
          $name = $names[$i];

          $out .= '<td align="center">';
          $out .= $name.': <b>'.$photo->get_author_display_name().'</b><br/>';
          $out .= $photo->get_attachment_link();
          $out .= '</td>';
        }
        $out .= '</tr></table>';
      }
    }
    return $out;
  }

}

$results = new EUHWCPhotoCompetition_Results;
add_shortcode('euhwc_photo_competition_results', array($results, 'shortcode'));

?>
