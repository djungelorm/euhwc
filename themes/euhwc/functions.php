<?php

// Add [image_cycle] shortcode to display WP Cycle plugin
function wp_cycle_func($atts) {
  return wp_cycle();
}
add_shortcode('image_cycle', 'wp_cycle_func');

// Remove twentythirteen widget areas
function euhwc_widgets_remove() {
  unregister_sidebar('sidebar-1');
  unregister_sidebar('sidebar-2');
}
add_action('widgets_init', 'euhwc_widgets_remove', 100);

// Register widget areas
function euhwc_widgets_init() {
  register_sidebar( array(
    'name'          => __('Side Widget Area', 'euhwc'),
    'id'            => 'sidebar-2',
    'description'   => __('Appears on the right hand size of posts and pages.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ));

  register_sidebar( array(
    'name'          => __('Front Page Main Widget Area', 'twentythirteen' ),
    'id'            => 'front-main',
    'description'   => __('Appears on the right hand side of the blurb on the homepage.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ) );

  register_sidebar( array(
    'name'          => __('Front Page Left Widget Area', 'twentythirteen' ),
    'id'            => 'front-left',
    'description'   => __('Appears on the left hand side of the front page.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ) );

  register_sidebar( array(
    'name'          => __('Front Page Middle Widget Area', 'twentythirteen' ),
    'id'            => 'front-middle',
    'description'   => __('Appears in the middle of the front page.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ) );

  register_sidebar( array(
    'name'          => __('Front Page Right Widget Area', 'twentythirteen' ),
    'id'            => 'front-right',
    'description'   => __('Appears on the right hand side of the front page.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ) );
}
add_action('widgets_init', 'euhwc_widgets_init', 101);

function euhwc_remove_twentythirteen_options() {
	remove_custom_background();
	remove_custom_image_header();
}
add_action('after_setup_theme', 'euhwc_remove_twentythirteen_options', 100);

// Customise the login page

function euhwc_login_style() {
  echo '<style type="text/css">
    body.login div#login h1 a {
      background-image: none;
    }
    </style>';
  wp_enqueue_style('euhwc_login', get_stylesheet_directory_uri() . '/css/login.css', false);
}

add_action ('login_enqueue_scripts', 'euhwc_login_style');

?>
