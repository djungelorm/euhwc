<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCPhotoCompetition_Photos {

  /** Register euhwc_pcomp_photo post type */
  public function register_post_type() {
    $args = array(
      'labels' => array(
        'name' => __('Photos'),
        'singular_name' => __('Photo'),
        'add_new' => __('Add New Photo'),
        'add_new_item' => __('Add New Photo'),
        'edit_item' => __('Edit Photo'),
        'new_item' => __('Add New Photo'),
        'all_items' => __('View Photos'),
        'view_item' => __('View Photo'),
        'search_items' => __('Search photos'),
        'not_found' =>  __('No photos found'),
        'not_found_in_trash' => __('No photos found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Photo Comp.')
      ),
      'public' => true,
      'has_archive' => true,
      'query_var' => true,
      'rewrite' => true,
      'capability_type' => 'post',
      'hierarchical' => false,
      'map_meta_cap' => true,
      'menu_position' => null,
      'supports' => array('title', 'author')
    );
    register_post_type('euhwc_pcomp_photo', $args);
  }

  /** Allowing sorting photos by votes in a WP_Query */
  public function orderby_votes($query) {
    if ($query->get('post_type') != 'euhwc_pcomp_photo')
      return;
    $orderby = $query->get('orderby');
    if ($orderby == 'votes') {
      $query->set('meta_key', 'photo_competition_num_votes');
      $query->set('orderby', 'meta_value_num');
    }
  }

  /**
   * Get the photos that have been submitted to the given category.
   * By default, for the current year and for all users.
   * If sorted is true, they are sorted in descending order of votes.
   */
  public static function get($category, $year = null, $user_id = null, $sorted = false, $random_order = false) {
    if ($year === null) {
      $year = date('Y');
    }
    $args = array(
      'post_type' => 'euhwc_pcomp_photo',
      'post_status' => 'publish',
      'year' => $year,
      'tax_query' => array(
        array(
          'taxonomy' => 'euhwc_pcomp_cat',
          'field' => 'term_id',
          'terms' => $category->term->term_id
        )
      )
    );
    if ($user_id !== null) {
      $args['author'] = $user_id;
    }
    $posts = new WP_Query($args);
    $f = function ($post) {
      return new EUHWCPhotoCompetition_Photo($post);
    };
    $result = array_map($f, $posts->posts);
    if ($sorted) {
      // Sort photos in descending order of votes
      $f = function ($a, $b) {
        return $b->get_num_votes() - $a->get_num_votes();
      };
      usort($result, $f);
    }
    if ($random_order) {
      // Sort photos into a stable random order
      srand(42);
      $order = array();
      for ($i = 0; $i < count($result); $i++) {
        $order[] = ($i * 3824624) % 200;
      }
      array_multisort($order, $result);
    }
    return $result;
  }

  /**
   * Add a photo. category is the taxonomy term, user is the user object of the author,
   * file_id is an index into the $_FILES array for the uploaded file.
   */
  public static function add($category, $user, $file_id) {

    // Check the user hasn't exceeded the entries limit
    $num_entries = count(self::get($category, null, $user->ID));
    $max_entries = EUHWCPhotoCompetition_Options::max_entries_per_category();
    if ($num_entries >= $max_entries) {
      return 'You\'ve already uploaded '.$max_entries.' photos to this category.';
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
      'post_type' => 'euhwc_pcomp_photo'
    );
    $post_id = wp_insert_post($data);
    assert($post_id != 0);

    // Set initial meta data
    add_post_meta($post_id, 'photo_competition_num_votes', 0);

    // Set the category
    $terms = wp_set_object_terms($post_id, $category->term->slug, 'euhwc_pcomp_cat');
    assert(is_array($terms) && count($terms) == 1 && $terms[0] == $category->term->term_id);

    // Attach the photo to the post
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
    $max_size = EUHWCPhotoCompetition_Options::max_upload_size();
    $valid_formats = EUHWCPhotoCompetition_Options::upload_valid_formats();
    $image_data = getimagesize($file['tmp_name']);
    if (!in_array($image_data['mime'], $valid_formats)) {
      $formats = EUHWCPhotoCompetition_Options::upload_valid_formats_human_readable();
      return 'Your logo must be in '.$formats.' format.';
    } elseif(($file['size'] > $max_size)) {
      return 'Your image was too large. It can be at most '.size_format($max_size).'.';
    }

    return true;
  }

}

$photos = new EUHWCPhotoCompetition_Photos;
add_action('init', array($photos, 'register_post_type'));
add_action('pre_get_posts', array($photos, 'orderby_votes'));

?>