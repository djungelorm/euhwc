<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCPhotoCompetition_Category {

  public $term;

  public function __construct($term) {
    assert($term->taxonomy == 'euhwc_pcomp_cat');
    $this->term = $term;
  }

  /** Vote for the given photos in the given category */
  public function vote_for($user_id, $year, $photos) {
    $this->clear_votes($user_id, $year);
    $result = true;
    foreach ($photos as $photo) {
      $result = $result && $photo->vote_for($user_id);
    }
    return $result;
  }

  /** Clear a users votes in this category */
  public function clear_votes($user_id, $year) {
    $photos = $this->get_photos_voted_for($user_id, $year);
    $result = true;
    foreach ($photos as $photo) {
      $result = $result && $photo->clear_vote($user_id);
    }
    return $result;
  }

  public function get_photos_voted_for($user_id, $year) {
    $photos = EUHWCPhotoCompetition_Photos::get($this, $year);
    $result = array();
    foreach ($photos as $photo) {
      if ($photo->has_users_vote($user_id))
        $result[] = $photo;
    }
    return $result;
  }

}

?>
