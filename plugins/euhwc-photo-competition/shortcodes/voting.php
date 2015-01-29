<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/** Allow users to vote for a photo */
class EUHWCPhotoCompetition_Voting {

  private $messages = array();

  /** Set up URL query arguments */
  function query_vars($vars) {
    $vars[] = 'category';
    return $vars;
  }

  public function process() {
    global $current_user;
    if (isset($_POST['euhwc_photo_competition_form_vote_submitted']) && isset($_POST['euhwc_photo_competition_category']) && isset($_POST['euhwc_photo_competition_year'])) {
      if (wp_verify_nonce($_POST['euhwc_photo_competition_form_vote_submitted'], 'euhwc_photo_competition_form_vote')) {
        $category = EUHWCPhotoCompetition_Categories::get_by_slug($_POST['euhwc_photo_competition_category']);
        $year = $_POST['euhwc_photo_competition_year'];
        $photos = array();
        if (isset($_POST['euhwc_photo_competition_vote'])) {
          foreach ($_POST['euhwc_photo_competition_vote'] as $post_id) {
            $photos[] = new EUHWCPhotoCompetition_Photo(get_post($post_id));
          }
        }
        $result = $category->vote_for($current_user->ID, $year, $photos);
        if ($result !== true) {
          $this->messages[] = '<div class="error">There was a problem saving your vote. '.$result.'</div>';
        } else {
          $this->messages[] = '<div class="success">Your vote has been saved.</div>';
        }
      } else {
        $this->messages[] = '<div class="error">There was a problem saving your vote. Internal error occured.</div>';
      }
    }
  }

  public function shortcode($atts) {
    global $current_user;
    if (!is_user_logged_in()) {
      wp_login_form();
      return '';
    }

    $atts = shortcode_atts(array('year' => date('Y')), $atts);

    $category = null;
    $category_slug = get_query_var('category');
    if ($category_slug) {
      $category = EUHWCPhotoCompetition_Categories::get_by_slug($category_slug);
      return $this->generate_category_table($atts['year'], $category);
    } else {
      return $this->generate_table($atts['year']);
    }
  }

  /** Generate table for casting votes */
  private function generate_table($year) {
    global $current_user;
    $out = '';
    $max_votes = EUHWCPhotoCompetition_Options::max_votes_per_category();
    $categories = EUHWCPhotoCompetition_Categories::get();

    if (count($categories) == 0) {
      return '<div class="error">There are no categories configured.</div>';
    }

    foreach ($categories as $category) {
      $out .= '<h2 style="display: inline; margin-right: 1em;">' . $category->term->name . '</h2>';
      $out .= '<p>'.$category->term->description.'</p>';

      $votes = $category->get_photos_voted_for($current_user->ID, $year);

      $out .= '<p>You have voted for <b>'.count($votes).' out of '.$max_votes.'</b> photos in this category. ';
      $out .= '<b><a href="'.esc_url(add_query_arg('category', $category->term->slug)).'">'.(count($votes) == 0 ? 'Place' : 'Update').' my Vote</a></b></p>';

      $out .= '<table><tr>';
      foreach ($votes as $photo) {
        $out .= '<td align="center">';
        $out .= $photo->get_attachment_link();
        $out .= '</td>';
      }
      for ($i = count($votes); $i < $max_votes; $i++) {
        $out .= '<td align="center">';
        $out .= '<img src="' . plugins_url('images/placeholder.png', dirname(__FILE__)) . '" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;"/>';
        $out .= '</td>';
      }
      $out .= '</tr></table>';
    }
    return $out;
  }

  /** Generate table for casting votes */
  private function generate_category_table($year, $category) {
    global $current_user;
    $out = '';
    $max_votes = EUHWCPhotoCompetition_Options::max_votes_per_category();
    $photos_per_row = 3;

    $out = '<p><a href="'.esc_url(remove_query_arg('category')).'">Back to the categories</a></p>';

    foreach ($this->messages as $message) {
      $out .= $message;
    }

    $out .= '<h2 style="display: inline; margin-right: 1em;">' . $category->term->name . '</h2>';
    $out .= $category->term->description;

    $out .= "<script>
function check_votes() {
  count = 0;
  votes = document.getElementById('euhwc_photo_competition_vote_form').elements['euhwc_photo_competition_vote[]'];
  for (x=0; x<votes.length; x++){
    if (votes[x].checked) {
      count++;
    }
  }
  if (count > ".$max_votes.") {
    alert('You can vote for a maximum of ".$max_votes." photos. Please uncheck some photos.');
    return false;
  } else {
    return true;
  }
}
</script>";

    $out .= '<form method="post" action="'.esc_url(add_query_arg(array())).'" id="euhwc_photo_competition_vote_form" onsubmit="return check_votes()">';
    $out .= wp_nonce_field('euhwc_photo_competition_form_vote', 'euhwc_photo_competition_form_vote_submitted');
    $out .= '<input type="hidden" name="euhwc_photo_competition_year" value="'.$year.'">';
    $out .= '<input type="hidden" name="euhwc_photo_competition_category" value="'.$category->term->slug.'">';

    $out .= '<p>To vote, select the photos you want to vote for and then click save. You can view a larger version of each photo by clicking on it.</p>';

    // Shuffle the photos into a stable random order
    $photos = EUHWCPhotoCompetition_Photos::get($category, $year, null, false, true);

    if (count($photos) == 0) {
      $out .= '<div class="error">There are no photos in this category!</div>';
    } else {
      $out .= '<p><input type="submit" name="submit" value="Save my Vote"/></p>';

      $out .= '<table><tr>';
      $i = 0;
      foreach ($photos as $photo) {
        $checked = '';
        $background = '#fff';
        if ($photo->has_users_vote($current_user->ID)) {
          $checked = ' checked="checked"';
          $background = '#8f8';
        }

        $out .= '<td align="center" id="euhwc_photo_competition_photo_td_'.$photo->post->ID.'" style="text-align: center; background-color: '.$background.';">';
        $out .= $photo->get_attachment_link();
        $out .= '<br/><input type="checkbox"'.$checked.' name="euhwc_photo_competition_vote[]" value="'.$photo->post->ID.'" id="euhwc_photo_competition_photo_'.$photo->post->ID.'" onclick="document.getElementById(\'euhwc_photo_competition_photo_td_'.$photo->post->ID.'\').style.backgroundColor = (document.getElementById(\'euhwc_photo_competition_photo_'.$photo->post->ID.'\').checked ? \'#8f8\' : \'#fff\');"/>';
        $out .= ' <label for="euhwc_photo_competition_photo_'.$photo->post->ID.'">Choose this photo</label>';
        $out .= '</td>';

        $i++;
        if ($i % $photos_per_row == 0) {
          $out .= '</tr><tr style="border-top: 1px solid #777;">';
        }
      }
      $out .= '</tr></table></form>';
    }
    return $out;
  }

}

$voting = new EUHWCPhotoCompetition_Voting;
add_action('the_post', array($voting, 'process'));
add_shortcode('euhwc_photo_competition_voting', array($voting, 'shortcode'));
add_filter('query_vars', array($voting, 'query_vars'));

?>
