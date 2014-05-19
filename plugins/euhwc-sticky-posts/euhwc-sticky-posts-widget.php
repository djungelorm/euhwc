<?php
/*
Plugin Name: EUHWC Sticky Posts
Description: A widget that shows the most recent stickied posts from a given category.
Version: 1.0
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
*/
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

class euhwc_sticky_posts_widget extends WP_Widget {

  var $defaults;
  var $icon_options;

  function __construct() {
    parent::__construct(
      'euhwc_sticky_posts_widget',
      'EUHWC Sticky Posts Widget',
      array('description' => 'Displays the most recent stickied posts from a chosen category.')
    );

    $this->defaults = array(
      'title' => 'Stickied',
      'categories' => array()
    );
  }

  public function widget($args, $instance) {
    $instance = array_merge($this->defaults, $instance);

    $sticky = get_option('sticky_posts');
    $query_args = array(
      'post__in'  => $sticky,
      'ignore_sticky_posts' => 1,
      'category__in'=> $instance['categories'],
    );
    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
      while ($query->have_posts()) {

        #TODO: display the title somewhere?
        #$title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];

        if (!empty($title))
          echo $args['before_title'] . $title . $args['after_title'];

        $query->the_post();
        echo '<h3>' . get_the_title() . '</h3>';
        echo '<p>' . get_the_content() . '</p>';

        echo $args['after_widget'];
      }
    }
  }

  public function form($instance) {
    $instance = array_merge($this->defaults, $instance);

    echo '<p><label for="' . $this->get_field_id('title') . '">' . __( 'Title:' ) . '</label>';
    echo '<input id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($instance['title']) . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('category') . '">' . __('Categories') . '</label>';
    $categories = get_categories('hide_empty=0');
    echo '<ul>';
    foreach ($categories as $category) {
      $checked = false;
      foreach ($instance['categories'] as $category2) {
        if ($category2 == $category->term_id) {
          $checked = true;
          break;
        }
      }
      echo '<li><input type="checkbox" id="'. $this->get_field_id('category-'+$category->term_id) . '" name="'. $this->get_field_name('categories') . '[]"' . ($checked ? ' checked="checked"' : '') . ' value="' . $category->term_id . '" /> ';
      echo '<label for="' . $this->get_field_id('category-'+$category->term_id) . '">' . $category->cat_name . '</label></li>';
    }
    echo '</ul></p>';
  }

  public function update($new_instance, $old_instance) {
    $new_instance = array_merge($this->defaults, $new_instance);
    $new_instance['title'] = strip_tags($new_instance['title']);
    return $new_instance;
  }
}

function euhwc_sticky_posts_widget_register() {
  register_widget('euhwc_sticky_posts_widget');
}

add_action('widgets_init', 'euhwc_sticky_posts_widget_register');

?>
