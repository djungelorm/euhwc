<?php
/*
Plugin Name: User Meta Widget
Description: A widget that provides links to log out and user account information.
Version: 1.3
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
      array('description' => 'Provides links to log out, account information and private pages that the user can access. The widget is hidden if the current user is not logged in.')
    );
  }

  public function widget($args, $instance) {
    if (!is_user_logged_in()) {
      return;
    }

    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( ! empty( $title ) ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }

    $current_user = wp_get_current_user();

    echo $instance['before_content'];
    echo '<ul>';

    // Show logged in as, log out link and current priviledges
    echo '<li><span class="icon-user"></span>Logged in as <i style="white-space: nowrap;">' . $current_user->display_name . '</i>';
    foreach ($current_user->roles as $role) {
      if ($role == get_option('default_role')) {
        continue;
      }
      echo '<li><span style="icon-none">You have <i>' .  $role . '</i> privileges</span></li>';
    }
    echo '<li><span style="icon-none"><a href="'.wp_logout_url($_SERVER['REQUEST_URI']).'">Log out</a></span></li>';

    // Display links to private pages that the user can access
    if (current_user_can('read_private_pages')) {
      $pages = get_pages(array('post_status' => 'private'));
      if ( ! empty( $pages ) ) {
        echo '<li>Private pages:</li>';
        foreach ($pages as $page) {
          echo '<li><span class="icon-post"><a href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a></span></li>';
        }
      }
    }

    //$output[] = '<a href="/edit-profile">Edit profile</a></li>';

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
