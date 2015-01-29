<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

class EUHWCPhotoCompetition_Categories {

  /** Register  custom taxonomy */
  public function register_taxonomy() {
    $args = array(
      'labels' => array(
        'name' => __('Categories'),
        'singular_name' => __('Category'),
        'all_items' => __('All Categories'),
        'edit_item' => __('Edit Category'),
        'view_item' => __('View Category'),
        'update_item' => __('Update Category'),
        'add_new_item' => __('Add New Category'),
        'new_item_name' => __('New Category Name'),
        //'search_items' => __('Search Categories'),
        //'popular_items' => __('Popular Categories'),
        //'add_or_remove_items' => __('Add or Remove Categories'),
        //'choose_from_most_used' => __('Choose from the most used categories'),
        //'not_found' => __('No categories found.')
      ),
      'public' => true,
      'show_ui' => true,
      'show_in_nav_menus' => false,
      'show_tagcloud' => false,
      'show_admin_column' => true,
      'hiearchical' => false
    );
    register_taxonomy(
      'euhwc_pcomp_cat',
      'euhwc_pcomp_photo',
      $args
    );
  }

  public static function get() {
    $args = array(
      'orderby' => 'id',
      'order' => 'ASC',
      'hide_empty' => false
    );
    $terms = get_terms('euhwc_pcomp_cat', $args);
    $f = function ($term) {
      return new EUHWCPhotoCompetition_Category($term);
    };
    return array_map($f, $terms);
  }

  public static function get_by_slug($slug) {
    return new EUHWCPhotoCompetition_Category(get_term_by('slug', $slug, 'euhwc_pcomp_cat'));
  }
}

$categories = new EUHWCPhotoCompetition_Categories;
add_action('init', array($categories, 'register_taxonomy'));

?>
