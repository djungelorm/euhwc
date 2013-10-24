<?php
/*
Plugin Name: EUHWC Recent Posts Widget
Description: Displays a list of recent posts, based on category.
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

class euhwc_recent_posts_widget extends WP_Widget {

  var $defaults;
  var $icon_options;

  function __construct() {
    parent::__construct(
      'euhwc_recent_posts_widget',
      'EUHWC Recent Posts Widget',
      array('description' => 'Displays a list of recent posts, based on category.')
    );

    $this->defaults = array(
      'title' => 'Recent Posts',
      'limit' => 5,
      'categories' => array(),
      'all_posts' => true,
      'all_posts_text' => 'Show all...',
      'no_posts_text' => 'No posts',
      'icon' => 'post'
    );

    $this->icon_options = array(
      'post' => __('Post'),
      'email' => __('Email'),
    );
  }

  public function widget($args, $instance) {
    $instance = array_merge($this->defaults, $instance);

    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if (!empty($title))
      echo $args['before_title'] . $title . $args['after_title'];

    $show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

    $query_args=array(
      'showposts' => $instance['limit'],
      'category__in'=> $instance['categories'],
    );
    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
      echo '<ul>';
      while ($query->have_posts()) {
        $query->the_post();
        echo '<li><span class="icon-' . $instance['icon'] . '"><a href="' . get_permalink() . '" rel="bookmark">' . get_the_title() . '</a></span>';
        echo '<span class="icon-time">' . get_the_date() . '</span></li>';
      }
      echo '</ul>';
    } else {
      echo '<p>' . $instance['no_posts_text'] . '</p>';
    }

    if ($instance['all_posts'] && count($instance['categories']) == 1) {
      echo '<p class="clearboth"><a href="' . get_category_link($instance['categories'][0]) . '">' . $instance['all_posts_text'] . '</a></p>';
    }

    echo $args['after_widget'];
  }

  public function form($instance) {
    $instance = array_merge($this->defaults, $instance);

    echo '<p><label for="' . $this->get_field_id('title') . '">' . __( 'Title:' ) . '</label>';
    echo '<input id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($instance['title']) . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('limit') . '">' . __('Number of posts') . '</label>';
    echo '<input id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" type="text" value="' . esc_attr($instance['limit']) . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('all_posts') . '">' . __('Show all posts link at bottom?') . '</label>';
    echo '<input type="checkbox" id="' . $this->get_field_id('all_posts') . '" name="' . $this->get_field_name('all_posts') . '" ' . ($instance['all_posts'] ? 'checked="checked"' : '') . '></p>';

    echo '<p><label for="' . $this->get_field_id('all_posts_text') . '">' . __('All posts link text') . '</label>';
    echo '<input type="text" id="' . $this->get_field_id('all_posts_text') . '" name="' . $this->get_field_name('all_posts_text') . '" value="' . $instance['all_posts_text'] . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('no_posts_text') . '">' . __('No posts text') . '</label>';
    echo '<input type="text" id="' . $this->get_field_id('no_posts_text') . '" name="' . $this->get_field_name('no_posts_text') . '" value="' . $instance['no_posts_text'] . '" /></p>';

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

    echo '<p><label for="' . $this->get_field_id('icon') . '">' . __('Post icon') . '</label>';
    echo '<select id="' . $this->get_field_id('icon') . '" name="' . $this->get_field_name('icon') . '">';
    foreach ($this->icon_options as $key => $value) {
      echo '<option value="' . $key . '"' . ($instance['icon'] == $key ? ' selected="selected"' : ''). '>' . $value . '</option>';
    }
    echo '</select></p>';
  }

  public function update($new_instance, $old_instance) {
    $new_instance = array_merge($this->defaults, $new_instance);
    $new_instance['title'] = strip_tags($new_instance['title']);
    $new_instance['number'] = absint($new_instance['number']);
    return $new_instance;
  }
}

function euhwc_recent_posts_widget_register() {
  register_widget('euhwc_recent_posts_widget');
}

add_action('widgets_init', 'euhwc_recent_posts_widget_register');

?>
