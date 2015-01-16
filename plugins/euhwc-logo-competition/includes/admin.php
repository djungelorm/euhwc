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

}

$admin = new EUHWCLogoCompetition_Admin;
add_action('admin_menu', array($admin, 'options_menu'));
add_action('do_meta_boxes', array($admin, 'image_box'));
add_action('add_meta_boxes', array($admin, 'votes_box'));

?>