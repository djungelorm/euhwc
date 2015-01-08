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
      'capability_type' => 'post',
      'hierarchical' => false,
      'map_meta_cap' => true,
      'menu_position' => null,
      'supports' => array('title', 'author', 'thumbnail')
    );

    register_post_type('euhwc_logocomp_entry', $args);
  }

  /** Get the number of logos that a user has submitted. By default, for the current year. */
  public static function num_entries($user_id = null, $year = null) {
    global $current_user;
    if ($user_id === null) {
      $user_id = $current_user->ID;
    }
    if ($year === null) {
      $year = date('Y');
    }
    $args = array(
      'author' => $user_id,
      'post_type' => 'euhwc_logocomp_entry',
      'post_status' => 'publish',
      'year' => $year
    );
    $user_images = new WP_Query($args);
    return $user_images->post_count;
  }

}

$logos = new EUHWCLogoCompetition_Logos;
add_action('init', array($logos, 'register_post_type'));

?>
