<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCLogoCompetition_Options {

  public static function max_upload_size() {
    $mb = 1024*1024;
    $max_upload = (int)(ini_get('upload_max_filesize')) * $mb;
    $max_post = (int)(ini_get('post_max_size')) * $mb;
    $memory_limit = (int)(ini_get('memory_limit')) * $mb;
    return min($max_upload, $max_post, $memory_limit);
  }

  public static function upload_valid_types() {
    return array(
      'image/jpeg',
      'image/png',
      'image/gif'
    );
  }

  public static function max_entries() {
    return get_option('max_entries', 5);
  }

  /** Set up options */
  public function options_init() {
    add_settings_section(
      'euhwc_logo_competition_setting_section',
      'General Settings',
      NULL,
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
    $max_entries = self::max_entries();
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

}

$options = new EUHWCLogoCompetition_Options;
add_action('admin_init', array($options, 'options_init'));

?>
