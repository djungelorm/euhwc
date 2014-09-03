<?php
/*
Plugin Name: EUHWC Events Widget
Description: A widget that provides events listing.
Version: 1.1
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

class euhwc_events_widget extends WP_Widget {
  var $defaults;
  var $order_options;
  var $orderby_options;

  public function __construct() {
    parent::__construct(
      'euhwc_events_widget',
      'EUHWC Events Widget',
      array('description' => 'Provides an events listing.')
    );

    $this->defaults = array(
      'title' => __('Events'), //TODO: do translations properly
      'scope' => 'future',
      'order' => 'ASC',
      'limit' => 5,
      'orderby' => 'event_start_date,event_start_time,event_name',
      'all_events' => true,
      'all_events_text' => __('Show all events'),
      'no_events_text' => __('No events')
    );

    $this->orderby_options = apply_filters(
      'em_settings_events_default_orderby_ddm',
      array(
        'event_start_date,event_start_time,event_name' => __('start date, start time, event name'),
        'event_name,event_start_date,event_start_time' => __('name, start date, start time'),
        'event_name,event_end_date,event_end_time' => __('name, end date, end time'),
        'event_end_date,event_end_time,event_name' => __('end date, end time, event name'),
      ));

    $this->order_options = apply_filters(
      'em_widget_order_ddm',
      array(
        'ASC' => __('Ascending'),
        'DESC' => __('Descending')
      ));
  }

  private function event_info($event, $show_excerpt = false, $thumbnail_size = array(120,120)) {
    $post = get_post($event->post_id);
    $time = $event->output('#_EVENTTIMES #_EVENTDATES');
    $facebook = $event->output('#_ATT{FacebookEvent}');
    $location = $event->location_id == 0 ? '' : $event->output('#_LOCATIONLINK');

    $output = array();

    $output[] = '<a href="' . $event->output('#_EVENTURL') . '">';
    $output[] = get_the_post_thumbnail($event->post_id, $thumbnail_size, array('class' => 'alignleft'));
    $output[] = '</a>';

    $output[] = '<span class="event-title">' . $event->output('#_EVENTLINK') . '</span><br/>';

    if ($time) {
      $output[] = '<span class="icon-time">' . $time . '</span>';
      if ($location)
        $output[] = ' ';
    }

    if ($location)
      $output[] = '<span class="icon-location nowrap">' . $location . '</span>';

    if ($time || $location)
      $output[] = '<br/>';

    if ($facebook)
      $output[] = '<a class="icon-facebook" href="' . $facebook . '" target="_blank">Facebook event</a><br/>';

    if ($show_excerpt)
       $output[] = $post->post_excerpt;

    return implode('', $output);
  }

  public function widget($args, $instance) {
    $instance = array_merge($this->defaults, $instance);

    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( ! empty( $title ) )
      echo $args['before_title'] . $title . $args['after_title'];

    $query_args = array(
      'scope' => $instance['scope'],
      'order'=> $instance['order'],
      'limit' => $instance['limit'],
      'orderby' => $instance['orderby']
    );
    $events = EM_Events::get($query_args);

    if (empty($events)) {
      echo '<p>' . $instance['no_events_text'] . '</p>';
    } else {

      echo '<ul>';

      // Display first event
      $event = array_shift($events);
      echo '<li>' . $this->event_info($event, true) . '</li>';

      // Display more events
      foreach ($events as $event)
        echo '<li>' . $this->event_info($event, false, array(70,70)) . '</li>';

      echo '</ul>';

      if ($instance['all_events'])
        echo '<p class="clearboth">' . em_get_link($instance['all_events_text']) . '</p>';
    }

    echo $args['after_widget'];
  }

  public function form($instance) {
    $instance = array_merge($this->defaults, $instance);

    echo '<p><label for="' . $this->get_field_id('title') . '">' . __( 'Title:' ) . '</label>';
    echo '<input id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($instance['title']) . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('limit') . '">' . __('Number of events') . '</label>';
    echo '<input id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" type="text" value="' . esc_attr($instance['limit']) . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('scope') . '">' . __('Scope') . '</label><br/>';
    echo '<select id="' . $this->get_field_id('scope') . '" name="' . $this->get_field_name('scope') . '" >';
    foreach(em_get_scopes() as $key => $value) {
      echo '<option value="' . $key . '" ' . ($key == $instance['scope'] ? 'selected="selected"' : '') . '>' . $value . '</option>';
    }
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('orderby') . '">' . __('Order By') . '</label>';
    echo '<select id="' . $this->get_field_id('orderby') . '" name="' . $this->get_field_name('orderby') . '">' . $this->orderby_options;
    foreach ($this->orderby_options as $key => $value) {
      echo '<option value="' . $key . '" '. ($key == $instance['orderby'] ? 'selected="selected"' : '') . '>' . $value . '</option>';
    }
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_name('order') . '">' . __('Order') . '</label>';
    echo '<select id="' . $this->get_field_id('order') . '" name="' . $this->get_field_name('order') . '">';
    foreach( $this->order_options as $key => $value) {
      echo '<option value="' . $key . '" ' . ($key == $instance['order'] ? 'selected="selected"' : '') . '>' . $value . '</option>';
    }
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('all_events') . '">' . __('Show all events link at bottom?') . '</label>';
    echo '<input type="checkbox" id="' . $this->get_field_id('all_events') . '" name="' . $this->get_field_name('all_events') . '" ' . ($instance['all_events'] ? 'checked="checked"' : '') . '></p>';

    echo '<p><label for="' . $this->get_field_id('all_events_text') . '">' . __('All events link text') . '</label>';
    echo '<input type="text" id="' . $this->get_field_id('all_events_text') . '" name="' . $this->get_field_name('all_events_text') . '" value="' . $instance['all_events_text'] . '" /></p>';

    echo '<p><label for="' . $this->get_field_id('no_events_text') . '">' . __('No events text') . '</label>';
    echo '<input type="text" id="' . $this->get_field_id('no_events_text') . '" name="' . $this->get_field_name('no_events_text') . '" value="' . $instance['no_events_text'] . '" /></p>';
  }

  public function update($new_instance, $old_instance) {
    $new_instance = array_merge($this->defaults, $new_instance);
    $new_instance['title'] = strip_tags($new_instance['title']);
    return $new_instance;
  }
}

function euhwc_events_widget_register() {
  register_widget('euhwc_events_widget');
}

add_action('widgets_init', 'euhwc_events_widget_register');

?>
