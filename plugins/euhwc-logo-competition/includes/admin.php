<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCLogoCompetition_Admin {

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
      'euhwc_logocomp_entry'
    );
  }

  /** Render input field for vote string */
  function votes_box_callback($post) {
    wp_nonce_field('euhwc_logo_competition_meta_box', 'euhwc_logo_competition_meta_box_nonce' );
    $value = get_post_meta($post->ID, 'logo_competition_vote', true);
    echo '<label for="euhwc_logo_competition_votes">';
    echo 'Vote string (DO NOT EDIT)';
    echo '</label> ';
    echo '<input type="text" id="euhwc_logo_competition_votes" name="euhwc_logo_competition_votes" value="' . esc_attr($value) . '" size="25" />';
  }

  /** Set up options */
  public function options_init() {
    add_settings_section(
      'euhwc_logo_competition_setting_section',
      'General Settings',
      none,
      'euhwc_logo_competition'
    );

    add_settings_field(
      'max_entries',
      'Maximum entries',
      array($this, 'max_entries_callback'),
      'euhwc_logo_competition',
      'euhwc_logo_competition_setting_section'
    );

    register_setting('euhwc_logo_competition', 'max_entries', array($this, 'max_entries_sanitize'));
  }

  /** Render input field for maximum entries option */
  public function max_entries_callback() {
    $max_entries = EUHWCLogoCompetition_Options::max_entries();
    echo '<input name="max_entries" id="max_entries" type="text" value="'.$max_entries.'" size="4" /> ';
    echo 'The maximum number of logos each user can submit, per year.';
  }

  /** Sanitize maximum entries option */
  public function max_entries_sanitize($value) {
    $value = intval($value);
    if ($value <= 0) {
      $value = 1;
    }
    return $value;
  }

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

}

$admin = new EUHWCLogoCompetition_Admin;
add_action('do_meta_boxes', array($admin, 'image_box'));
add_action('add_meta_boxes', array($admin, 'votes_box'));
add_action('admin_init', array($admin, 'options_init'));
add_action('admin_menu', array($admin, 'options_menu'));

?>
