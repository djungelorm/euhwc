<?php
/**
 * Template Name: Blank Page Template
 */
?>

<html>
  <head>
    <title>Edinburgh University Hillwalking Club</title>
  </head>
  <style type="text/css">
  body {
    background-color: #fff;
    font-size: 12pt;
    font-family: Arial, Helvetica, sans-serif;
  }

  #page {
    max-width: 650px;
    margin: 0px auto;
    margin-top: 30px;
    border: 1px solid #ccc;
    padding: 10px 20px;
  }

  .site-header {
    display: block;
    white-space: nowrap;
    margin: 0px auto;
    max-width: 600px;
    vertical-align: middle;
    text-decoration: none;
    padding-bottom: 5px;
  }

  .site-logo {
    display: inline-block;
    vertical-align: middle;
    padding-right: 2px;
  }

  .site-title {
    display: inline-block;
    vertical-align: middle;
    font-weight: bold;
    font-size: 18pt;
    color: #222;
  }

  .site-title:hover {
    color: #222;
  }

  a, a:visited {
    color: #55f;
  }

  a:active, a:hover {
    color: #116;
  }
  </style>
<body>

  <div id="page">
    <a class="site-header" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
      <img class="site-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/images/euhwc-logo.png" alt="<?php bloginfo( 'name' ); ?>"/>
      <h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
    </a>

<?
while (have_posts()) {
  the_post();
  the_content();
}
?>

  </div>
</body>
</html>