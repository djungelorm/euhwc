<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCPhotoCompetition_Admin {

  /** Register options page */
  public function options_menu() {
    add_options_page('Photo Competition Options', 'Photo Competition', 'manage_options', 'euhwc_photo_competition_options', array($this, 'options_page'));
  }

  /** Render options page */
  public function options_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__( 'You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<h2>Photo Competition Options</h2>';
    echo '<form method="post" action="options.php">';
    settings_fields('euhwc_photo_competition');
    do_settings_sections('euhwc_photo_competition');
    submit_button();
    echo '</form>';
    echo '</div>';
  }

  /** Customize the columns on the admin edit pages */
  public function manage_edit_columns($columns) {
    $first_columns = array_splice($columns, 0, 4);
    $columns = array_merge($first_columns, array('votes' => __('Votes')), $columns);
    return $columns;
  }

  public function manage_posts_custom_column($column) {
    global $post;
    if ($column == 'votes') {
      $photo = new EUHWCPhotoCompetition_Photo($post);
      echo $photo->get_num_votes();
    }
  }

  public function manage_edit_sortable_columns($columns) {
    $columns['votes'] = 'votes';
    return $columns;
  }

  /** Move 'Featured Image' box to main column of admin page, and rename it to 'Photo' */
  public function image_box() {
    remove_meta_box('postimagediv', 'euhwc_pcomp_photo', 'side');
    add_meta_box('postimagediv', __('Photo'), 'post_thumbnail_meta_box', 'euhwc_pcomp_photo', 'normal', 'high');
  }

  /** Add vote string box to edit/add admin pages */
  public function votes_box() {
    add_meta_box(
      'euhwc_photo_competition_votes',
      __('Votes'),
      array($this, 'votes_box_callback'),
      'euhwc_pcomp_photo',
      'side'
    );
  }


  /** Render input field for vote string */
  function votes_box_callback($post) {
    $values = get_post_meta($post->ID, 'photo_competition_vote', false);
    $votes = count($values);
    if ($votes == 0) {
      echo '<p>There are no votes for this photo.</p>';
    } else {
      if ($votes == 1) {
        echo '<p>There is 1 vote, by the following person:</p>';
      } else {
        echo '<p>There are ' . $votes . ' votes, by the following people:</p>';
      }
      echo '<select multiple="multiple" size="5">';
      foreach ($values as $uid) {
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

$admin = new EUHWCPhotoCompetition_Admin;
add_action('admin_menu', array($admin, 'options_menu'));
add_action('manage_edit-euhwc_pcomp_photo_columns', array($admin, 'manage_edit_columns'));
add_action('manage_euhwc_pcomp_photo_posts_custom_column', array($admin, 'manage_posts_custom_column'));
add_action('manage_edit-euhwc_pcomp_photo_sortable_columns', array($admin, 'manage_edit_sortable_columns'));
add_action('do_meta_boxes', array($admin, 'image_box'));
add_action('add_meta_boxes', array($admin, 'votes_box'));

?>
