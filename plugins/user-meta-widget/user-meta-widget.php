<?php
/*
Plugin Name: User Meta Widget
Description: A widget that provides log out and edit profile links.
Version: 1.2
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

class user_meta_widget extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'user_meta_widget',
      'User Meta',
      array('description' => 'Provides log out and edit profile links, and is hidden when the user is not logged in.')
    );
  }

  public function widget($args, $instance) {
    if (!is_user_logged_in())
      return;

    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( ! empty( $title ) )
      echo $args['before_title'] . $title . $args['after_title'];

    $current_user = wp_get_current_user();

    echo $instance['before_content'];
    echo '<ul>';
    echo '<li>Logged in as <i>' . $current_user->display_name . '</i></li>';
    foreach ($current_user->roles as $role) {
      if ($role == get_option('default_role'))
        continue;
      echo '<li>You have <i>' .  $role . '</i> privileges</li>';
    }

    if (current_user_can('read_private_pages')) {
      $pages = get_pages(array('post_status' => 'private'));
      foreach ($pages as $page) {
        echo '<li><a href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a></li>';
      }
    }


    //$output[] = '<a href="/edit-profile">Edit profile</a></li>';
    echo '<li><a href="'.wp_logout_url($_SERVER['REQUEST_URI']).'">Log out</a></li>';
    echo '</ul>';
    echo $instance['after_content'];
    echo $args['after_widget'];
  }

  public function form($instance) {
    $title = '';
    $before_content = '';
    $after_content = '';
    if (isset($instance['title'])) {
      $title = $instance['title'];
    }
    if (isset($instance['before_content'])) {
      $before_content = $instance['before_content'];
    }
    if (isset($instance['after_content'])) {
      $after_content = $instance['after_content'];
    }

    echo '<p>';

    echo '<label for="' . $this->get_field_name('title') . '">' . _e( 'Title:' ) . '</label>';
    echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '" />';

    echo '<label for="' . $this->get_field_name('before_content') . '">' . _e( 'Insert HTML before content:' ) . '</label>';
    echo '<textarea class="widefat" id="' . $this->get_field_id('before_content') . '" name="' . $this->get_field_name('before_content') . '">' . esc_attr($before_content) . '</textarea>';

    echo '<label for="' . $this->get_field_name('after_content') . '">' . _e( 'Insert HTML after content:' ) . '</label>';
    echo '<textarea class="widefat" id="' . $this->get_field_id('after_content') . '" name="' . $this->get_field_name('after_content') . '">' . esc_attr($after_content) . '</textarea>';

    echo '</p>';
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    $instance['before_content'] = (!empty($new_instance['before_content'])) ? strip_tags($new_instance['before_content']) : '';
    $instance['after_content'] = (!empty($new_instance['after_content'])) ? strip_tags($new_instance['after_content']) : '';
    return $instance;
  }
}

function user_meta_widget_register() {
  register_widget('user_meta_widget');
}

add_action('widgets_init', 'user_meta_widget_register');

?>
