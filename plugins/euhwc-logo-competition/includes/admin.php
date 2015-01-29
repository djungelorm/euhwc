<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCLogoCompetition_Admin {

  /** Register options page */
  public function options_menu() {
    add_options_page('Logo Competition Options', 'Logo Competition', 'manage_options', 'euhwc_logo_competition_options', array($this, 'options_page'));
  }

  /** Render options page */
  public function options_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__( 'You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<h2>Logo Competition Options</h2>';
    echo '<form method="post" action="options.php">';
    settings_fields('euhwc_logo_competition');
    do_settings_sections('euhwc_logo_competition');
    submit_button();
    echo '</form>';
    echo '</div>';
  }

  /** Move 'Featured Image' box to main column of admin page, and rename it to 'Logo' */
  public function image_box() {
    remove_meta_box('postimagediv', 'euhwc_logocomp_entry', 'side');
    add_meta_box('postimagediv', __('Logo'), 'post_thumbnail_meta_box', 'euhwc_logocomp_entry', 'normal', 'high');
  }

  /** Add vote string box to edit/add admin pages */
  public function votes_box() {
    add_meta_box(
      'euhwc_logo_competition_votes',
      __('Votes'),
      array($this, 'votes_box_callback'),
      'euhwc_logocomp_entry',
      'side'
    );
  }

  /** Render input field for vote string */
  function votes_box_callback($post) {
    $logo = new EUHWCLogoCompetition_Logo($post);
    $votes = $logo->get_votes();
    $num_votes = count($votes);
    if ($num_votes == 0) {
      echo '<p>There are no votes for this logo.</p>';
    } else {
      if ($num_votes == 1) {
        echo '<p>There is 1 vote, by the following person:</p>';
      } else {
        echo '<p>There are ' . $num_votes . ' votes, by the following people:</p>';
      }
      echo '<select multiple="multiple" size="5">';
      foreach ($votes as $uid) {
        $user = get_userdata($uid);
        if ($user === false) {
          $name = 'Unknown user';
        } else {
          $name = $user->display_name;
        }
        echo '<option>' . esc_attr($name) . '</option>';
      }
      echo '</select>';
    }
  }
}

$admin = new EUHWCLogoCompetition_Admin;
add_action('admin_menu', array($admin, 'options_menu'));
add_action('do_meta_boxes', array($admin, 'image_box'));
add_action('add_meta_boxes', array($admin, 'votes_box'));

?>
