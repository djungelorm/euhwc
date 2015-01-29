<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCLogoCompetition_Logo {

  public $post;

  public function __construct($post) {
    assert($post->post_type == 'euhwc_logocomp_entry');
    $this->post = $post;
  }

  public function get_votes() {
    return get_post_meta($this->post->ID, 'logo_competition_vote', false);
  }

  public function get_num_votes() {
    return intval(get_post_meta($this->post->ID, 'logo_competition_num_votes', true));
  }

  public function get_attachment_link() {
    $post_thumbnail_id = get_post_thumbnail_id($this->post->ID);
    return wp_get_attachment_link($post_thumbnail_id, 'thumbnail');
  }

  public function get_author_display_name() {
    return get_the_author_meta('display_name', $this->post->post_author);
  }

  public function delete() {
    if ($post_thumbnail_id = get_post_thumbnail_id($this->post->ID)) {
      wp_delete_attachment($post_thumbnail_id, true);
    }
    wp_delete_post($this->post->ID, true);
  }

  /** Returns true if the user with then given user id has voted for this logo */
  public function has_users_vote($user_id) {
    return in_array($user_id, get_post_meta($this->post->ID, 'logo_competition_vote', false));
  }

  /** Set the users vote for this logo */
  public function vote_for($user_id, $year) {
    // Clear all the users current votes
    $logos = EUHWCLogoCompetition_Logos::get($year);
    foreach ($logos as $logo) {
      $logo->clear_vote($user_id);
    }
    // Add the new vote
    $result = add_post_meta($this->post->ID, 'logo_competition_vote', $user_id);
    // Update the vote count
    $this->update_num_votes();
    return $result;
  }

  /** Clear the users vote for this logo */
  public function clear_vote($user_id) {
    // Remove the uid from the vote list
    $result = delete_post_meta($this->post->ID, 'logo_competition_vote', $user_id);
    // Update the vote count
    $this->update_num_votes();
    return $result;
  }

  /** Update the num_votes meta data from the votes list */
  private function update_num_votes() {
    $n = count($this->get_votes());
    update_post_meta($this->post->ID, 'logo_competition_num_votes', $n);
  }
}

?>
