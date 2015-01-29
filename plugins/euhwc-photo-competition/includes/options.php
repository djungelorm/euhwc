<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCPhotoCompetition_Options {

  public static function max_upload_size() {
    $mb = 1024*1024;
    $max_upload = (int)(ini_get('upload_max_filesize')) * $mb;
    $max_post = (int)(ini_get('post_max_size')) * $mb;
    $memory_limit = (int)(ini_get('memory_limit')) * $mb;
    return min($max_upload, $max_post, $memory_limit);
  }

  public static function upload_valid_formats() {
    return array(
      'image/jpeg',
      'image/png'
    );
  }

  public static function upload_valid_formats_human_readable() {
    $formats = array();
    foreach (self::upload_valid_formats() as $format) {
      array_push($formats, strtoupper(explode('/',$format)[1]));
    }
    return join(array_slice($formats, 0, -1), ', ').' or '.$formats[count($formats)-1];
  }

  public static function max_entries_per_category() {
    return get_option('max_entries_per_category', 3);
  }

  public static function max_votes_per_category() {
    return get_option('max_votes_per_category', 2);
  }

  /** Set up options */
  public function options_init() {
    add_settings_section(
      'euhwc_photo_competition_setting_section',
      'General Settings',
      NULL,
      'euhwc_photo_competition'
    );

    add_settings_field(
      'max_entries_per_category',
      'Max. entries per category',
      array($this, 'max_entries_per_category_callback'),
      'euhwc_photo_competition',
      'euhwc_photo_competition_setting_section'
    );
    register_setting('euhwc_photo_competition', 'max_entries_per_category', array($this, 'positive_integer_sanitize'));

    add_settings_field(
      'max_votes_per_category',
      'Max. votes per category',
      array($this, 'max_votes_per_category_callback'),
      'euhwc_photo_competition',
      'euhwc_photo_competition_setting_section'
    );
    register_setting('euhwc_photo_competition', 'max_votes_per_category', array($this, 'positive_integer_sanitize'));
  }

  /** Render input field for maximum entries per category option */
  public function max_entries_per_category_callback() {
    $n = self::max_entries_per_category();
    echo '<input name="max_entries_per_category" id="max_entries_per_category" type="text" value="'.$n.'" size="4" /> ';
    echo 'The maximum number of photos each user can submit, to each category, per year.';
  }

  /** Render input field for maximum votes per category option */
  public function max_votes_per_category_callback() {
    $n = self::max_votes_per_category();
    echo '<input name="max_votes_per_category" id="max_votes_per_category" type="text" value="'.$n.'" size="4" /> ';
    echo 'The maximum number of photos each user can vote for, in each category, per year.';
  }

  /** Sanitize positive integer options */
  public function positive_integer_sanitize($value) {
    $value = intval($value);
    if ($value <= 0) {
      $value = 1;
    }
    return $value;
  }

}

$options = new EUHWCPhotoCompetition_Options;
add_action('admin_init', array($options, 'options_init'));

?>
