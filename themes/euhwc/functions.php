<?php

/**
 * Hide the admin bar unless the user can publish posts
 */
function euhwc_hide_admin_bar() {
  return current_user_can('publish_posts');
}
add_filter('show_admin_bar', 'euhwc_hide_admin_bar');

/**
 * Add [image_cycle] shortcode to display WP Cycle plugin
 */
function wp_cycle_func($atts) {
  return wp_cycle();
}
add_shortcode('image_cycle', 'wp_cycle_func');

/**
 * Customise feeds
 */
function euhwc_feed_links() {
  echo '<link rel="alternate" type="application/rss+xml" title="';
  echo bloginfo('site_name');
  echo '" href="';
  echo bloginfo('rss_url');
  echo '" />';
  echo "\n";
}
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
add_action('wp_head', 'euhwc_feed_links', 2);

/**
 * Remove twentythirteen widget areas
 */
function euhwc_widgets_remove() {
  unregister_sidebar('sidebar-1');
  unregister_sidebar('sidebar-2');
}
add_action('widgets_init', 'euhwc_widgets_remove', 100);

/**
 * Register widget areas
 */
function euhwc_widgets_init() {
  register_sidebar( array(
    'name'          => __('Side Widget Area', 'euhwc'),
    'id'            => 'sidebar-2',
    'description'   => __('Appears on the right hand side of posts and pages.', 'euhwc'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ));

  register_sidebar( array(
    'name'          => __('Floating Widget Area', 'euhwc'),
    'id'            => 'sidebar-floating',
    'description'   => __('Appears on the right hand side at the top, floating so that is always visible.', 'euhwc'),
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
	remove_theme_support('custom-background');
	remove_theme_support('custom-header');
}

add_action('after_setup_theme', 'euhwc_remove_twentythirteen_options', 100);

/**
 * Customise the login page
 */
function euhwc_login_style() {
  echo '<style type="text/css">
    body.login div#login h1 a {
      background-image: none;
    }
    </style>';
  wp_enqueue_style('euhwc_login', get_stylesheet_directory_uri() . '/css/login.css', false);
}

add_action ('login_enqueue_scripts', 'euhwc_login_style');

// Redirect private pages to login page if the user is not logged in
function intercept_private_page($posts, &$wp_query) {
  // remove filter for subsequent post querying
  remove_filter('the_posts', 'intercept_private_page', 5, 2);

  // Stop if the user can read private pages
  if (current_user_can('read_private_pages'))
    return $posts;

  // Stop if no post was queried
  if (!($wp_query->is_page && empty($posts)))
    return $posts;

  // Check if the page is private
  if (!empty($wp_query->query['page_id']))
    $page = get_page($wp_query->query['page_id']);
  else
    $page = get_page_by_path($wp_query->query['pagename']);

  // Redirect to login if the page is private and the user is not logged int
  if (!is_user_logged_in() && $page && $page->post_status == 'private') {
    wp_redirect(wp_login_url(get_permalink($page->ID)), 301);
    exit;
  }

  return $posts;
}
add_filter('the_posts', 'intercept_private_page', 5, 2);

?>
