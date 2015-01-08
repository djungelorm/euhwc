<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Move 'Featured Image' box to main column of admin page, and rename it to 'Logo'
 */

add_action('do_meta_boxes', 'euhwc_logo_competition_admin_image_box');

function euhwc_logo_competition_admin_image_box() {
  remove_meta_box('postimagediv', 'euhwc_logocomp_entry', 'side');
  add_meta_box('postimagediv', __('Logo'), 'post_thumbnail_meta_box', 'euhwc_logocomp_entry', 'normal', 'high');
}

/**
 * Add vote string box to edit/add admin pages
 */

add_action('add_meta_boxes', 'euhwc_logo_competition_admin_votes_box');

function euhwc_logo_competition_admin_votes_box() {
  add_meta_box(
    'euhwc_logo_competition_votes',
    __('Votes'),
    'euhwc_logo_competition_admin_votes_box_callback',
    'euhwc_logocomp_entry'
  );
}

function euhwc_logo_competition_admin_votes_box_callback($post) {
  wp_nonce_field('euhwc_logo_competition_meta_box', 'euhwc_logo_competition_meta_box_nonce' );
  $value = get_post_meta($post->ID, 'logo_competition_vote', true);
  echo '<label for="euhwc_logo_competition_votes">';
  echo 'Vote string (DO NOT EDIT)';
  echo '</label> ';
  echo '<input type="text" id="euhwc_logo_competition_votes" name="euhwc_logo_competition_votes" value="' . esc_attr($value) . '" size="25" />';
}

?>
