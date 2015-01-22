<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCLogoCompetition_Logos {

  /** Register euhwc_logocomp_entry custom post type */
  public function register_post_type() {
    $args = array(
      'labels' => array(
       'name' => __('Logos'),
        'singular_name' => __('Logo'),
        'add_new' => __('Add New Logo'),
        'add_new_item' => __('Add New Logo'),
        'edit_item' => __('Edit Logo'),
        'new_item' => __('Add New Logo'),
        'all_items' => __('View Logos'),
        'view_item' => __('View Logo'),
        'search_items' => __('Search Logos'),
        'not_found' =>  __('No Logos found'),
        'not_found_in_trash' => __('No Logos found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Logo Comp.')
      ),
      'public' => true,
      'has_archive' => true,
      'query_var' => true,
      'rewrite' => true,
      'capability_type' => 'logo',
      'hierarchical' => false,
      'map_meta_cap' => true,
      'menu_position' => null,
      'supports' => array('title', 'author', 'thumbnail')
    );

    $role = get_role('administrator');
    $role->add_cap('add_logos');
    $role->add_cap('edit_logos');
    $role->add_cap('delete_logos');

    register_post_type('euhwc_logocomp_entry', $args);
  }

  /**
   * Get the logos that have been submitted. By default, for the current year and for all users.
   * If sorted is true, they are sorted in descending order of votes.
   */
  public static function get($year = null, $user_id = null, $sorted = false) {
    if ($year === null) {
      $year = date('Y');
    }
    $args = array(
      'post_type' => 'euhwc_logocomp_entry',
      'post_status' => 'publish',
      'year' => $year
    );
    if ($user_id !== null) {
      $args['author'] = $user_id;
    }
    $posts = new WP_Query($args);
    $f = function ($post) {
      return new EUHWCLogoCompetition_Logo($post);
    };
    // Sort logos in descending order of votes
    $result = array_map($f, $posts->posts);
    if ($sorted) {
      $f = function ($a, $b) {
        return $b->get_num_votes() - $a->get_num_votes();
      };
      usort($result, $f);
    }
    return $result;
  }

  /** Get a logo based on its post id. */
  public static function get_logo_by_id($post_id) {
    return new EUHWCLogoCompetition_Logo(get_post($post_id));
  }

  /**
   * Add a logo. user is the user object of the author, file_id is an index into
   * the $_FILES array for the uploaded file.
   */
  public static function add($user, $file_id) {
    // Check the user hasn't exceeded the entries limit
    $num_entries = count(self::get(null, $user->ID));
    $max_entries = EUHWCLogoCompetition_Options::max_entries();
    if ($num_entries >= $max_entries) {
      return 'You\'ve already uploaded '.$max_entries.' logos.';
    }

    // Validate the file upload
    $result = self::validate_upload($_FILES[$file_id]);
    if ($result !== true) {
      return $result;
    }

    // Create a post
    $data = array(
      'post_title' => $user->display_name.' ('.$_FILES[$file_id]['name'].')',
      'post_status' => 'publish',
      'post_author' => $user->ID,
      'post_type' => 'euhwc_logocomp_entry'
    );
    $post_id = wp_insert_post($data);
    assert($post_id != 0);

    // Attach the image to the post
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $attachment_id = media_handle_upload($file_id, $post_id);
    update_post_meta($post_id, '_thumbnail_id', $attachment_id);
    $attachment_data = array(
      'ID' => $attachment_id,
      'post_excerpt' => ''
    );
    wp_update_post($attachment_data);

    return true;
  }

  /** Check a file upload request is valid and return a result array summarising the submission */
  private static function validate_upload($file) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
      if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'Your image was too large.';
      } else if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'Please choose a file and try again.';
      } else {
        return 'There was an error uploading your file!';
      }
    }

    // Check the type and size of the image
    $max_size = EUHWCLogoCompetition_Options::max_upload_size();
    $valid_formats = EUHWCLogoCompetition_Options::upload_valid_formats();
    $image_data = getimagesize($file['tmp_name']);
    if (!in_array($image_data['mime'], $valid_formats)) {
      $formats = EUHWCLogoCompetition_Options::upload_valid_formats_human_readable();
      return 'Your logo must be in '.$formats.' format.';
    } elseif(($file['size'] > $max_size)) {
      return 'Your image was too large. It can be at most '.size_format($max_size).'.';
    }

    return true;
  }

}

$logos = new EUHWCLogoCompetition_Logos;
add_action('init', array($logos, 'register_post_type'));

?>
