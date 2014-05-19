<?php
/*
Plugin Name: EUHWC Photo Competition
Description: EUHWC Photo Competition
Version: 1.0
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
*/

require 'aws/aws-autoloader.php';
use Aws\S3\S3Client;

// TODO: Move these into the admin interface
define('PHOTOCOMP_CATEGORIES', serialize(array(
  'landscape'    => array('title' => 'Landscape',
                          'description' => 'Photos of the beautiful Scottish scenery. Can include people, but landscape/nature should be the main focus.'),
  'on_the_hill'  => array('title' => 'On the Hill',  'description' => 'Photos of the hills, with people as the main focus.'),
  'off_the_hill' => array('title' => 'Off the Hill', 'description' => 'Photos from socials and any other "off the hill" antics.'),
  'looking_good' => array('title' => 'Looking Good',
                          'description' => 'People <em>looking good</em> in either a good or a bad way ;) '.
                                           'Please get consent from the unfortunate person.'),
  'extreme'      => array('title' => 'Extreme!',
                          'description' => 'Epic walks, insane exposure, severe weather or anything else you consider extreme!')
)));
define('PHOTOCOMP_MAX_UPLOAD_SIZE', 10*1024*1024);
define('PHOTOCOMP_TYPE_WHITELIST', serialize(array(
  'image/jpeg',
  'image/png',
  'image/gif'
)));

add_action('init', 'euhwc_photo_competition_init');

function euhwc_photo_competition_init() {
  $args = array(
      'labels' => array(
        'name' => __('Photos'),
        'singular_name' => __('Photo'),
        'add_new' => __('Add New Photo'),
        'add_new_item' => __('Add New Photo'),
        'edit_item' => __('Edit Photo'),
        'new_item' => __('Add New Photo'),
        'all_items' => __('View Photos'),
        'view_item' => __('View Photo'),
        'search_items' => __('Search photos'),
        'not_found' =>  __('No photos found'),
        'not_found_in_trash' => __('No photos found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Photo Comp.')
      ),
    'public' => true,
    'has_archive' => true,
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'map_meta_cap' => true,
    'menu_position' => null,
    'supports' => array('title', 'editor', 'author', 'thumbnail')
  );

  register_post_type('euhwc_photocom_entry', $args);
}

add_shortcode('euhwc_photo_competition_submission', 'euhwc_photo_competition_submission_shortcode');
add_shortcode('euhwc_photo_competition_entries', 'euhwc_photo_competition_entries_shortcode');
add_shortcode('euhwc_photo_competition_voting', 'euhwc_photo_competition_voting_shortcode');
add_shortcode('euhwc_photo_competition_results', 'euhwc_photo_competition_results_shortcode');

function euhwc_photo_competition_query_vars( $vars ){
  $vars[] = 'category';
  return $vars;
}
add_filter('query_vars', 'euhwc_photo_competition_query_vars');

function euhwc_photo_competition_get_all_entries($categories) {
  // Get user ids of everyone who has submitted a photo
  $args = array(
    'post_type' => 'euhwc_photocom_entry',
    'post_status' => 'publish',
    'nopaging' => TRUE
  );
  $photos = new WP_Query($args);
  $users = array();
  foreach ($photos->posts as $photo) {
    array_push($users, $photo->post_author);
  }
  $users = array_unique($users);

  // Get the entries for all users
  $data = array();
  foreach ($users as $user_id) {
    $data[$user_id] = euhwc_photo_competition_get_entries($user_id, $categories);
  }
  return $data;
}

function euhwc_photo_competition_get_entries($user_id, $categories) {
  $data = array();
  foreach ($categories as $key => $value) {
    $data[$key] = $value;
    $data[$key]['images'] = array(FALSE,FALSE,FALSE);
  }

  $args = array(
    'author' => $user_id,
    'post_type' => 'euhwc_photocom_entry',
    'post_status' => 'publish',
    'nopaging' => TRUE
  );

  $photos = new WP_Query($args);

  if (!$photos->post_count)
    return $data;

  foreach ($photos->posts as $photo) {
    $category = get_post_meta($photo->ID, 'category_id', true);
    $id = get_post_meta($photo->ID, 'image_id', true);
    $data[$category]['images'][$id]['post_id'] = $photo->ID;
    $data[$category]['images'][$id]['full'] = 'https://s3-eu-west-1.amazonaws.com/photos.euhwc.eusu.ed.ac.uk/'.get_post_meta($photo->ID, 'photo_url', true);
    $data[$category]['images'][$id]['thumb'] = 'https://s3-eu-west-1.amazonaws.com/photos.euhwc.eusu.ed.ac.uk/'.get_post_meta($photo->ID, 'photo_thumbnail_url', true);
    $data[$category]['images'][$id]['votes'] = get_post_meta($photo->ID, 'photo_competition_vote', false);
    $data[$category]['images'][$id]['name'] = get_the_author_meta('first_name', $photo->post_author) . ' ' . get_the_author_meta('last_name', $photo->post_author);
  }

  return $data;
}

function euhwc_photo_competition_submission_shortcode() {
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  global $current_user;

  $messages = array();
  $categories = unserialize(PHOTOCOMP_CATEGORIES);

  // Process photo submissions
  if (isset($_POST['euhwc_photo_competition_upload_form_submitted']) &&
      wp_verify_nonce($_POST['euhwc_photo_competition_upload_form_submitted'], 'euhwc_photo_competition_upload_form')) {
    foreach ($categories as $category => $value) {
      for ($id = 0; $id < 3; $id++) {
        // Look for a new image
        $key = 'euhwc_photo_competition_file_'.$category.'_'.$id;
        if (array_key_exists($key, $_FILES) && $_FILES[$key]['name']) {
          $file = $_FILES[$key];
          // Save it to disk and database
          $result = euhwc_photo_competition_save_photo($category, $id, $file);
          if ($result !== TRUE)
            array_push($messages, 'Failed to upload photo. '.$result['error']);
        }
      }
    }
  }

  // Process photo deletions
  foreach ($categories as $category => $value) {
    for ($id = 0; $id < 3; $id++) {
      $key = 'euhwc_photo_competition_delete_'.$category.'_'.$id;
      if (array_key_exists($key, $_POST) &&
          wp_verify_nonce($_POST['euhwc_photo_competition_upload_form_submitted'], 'euhwc_photo_competition_upload_form')) {
        $result = euhwc_photo_competition_delete_photo($current_user->ID, $category, $id);
        if ($result !== TRUE)
          array_push($messages, 'Failed to delete photo. '.$result['error']);
      }
    }
  }

  $out = '';

  foreach ($messages as $message) {
    $out .= '<div class="error">'.$message.'</div>';
  }

  // Load previously submitted images from the database.
  $data = euhwc_photo_competition_get_entries($current_user->ID, $categories);

  $out .= '<form id="euhwc_photo_competition_upload_form" method="post" action="" enctype="multipart/form-data">';

  foreach ($data as $category => $value) {
    $out .= '<h2 style="display: inline; margin-right: 1em;">' . $value['title'] . '</h2>';
    $out .= $value['description'];
    $images = $value['images'];

    $out .= wp_nonce_field('euhwc_photo_competition_upload_form', 'euhwc_photo_competition_upload_form_submitted');
    $out .= '<table style="border-bottom: 0px;"><tr>';
    foreach ($images as $id => $image) {
      if ($image)
        $out .= '<td align="center" style="border: 0px;"><a href="' . $image['full'] . '" class="thickbox"><img src="' . $image['thumb'] . '" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;" /></a></td>';
      else
        $out .= '<td align="center" style="border: 0px;"><img src="' . plugins_url( 'placeholder.png' , __FILE__ ) . '" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;"/></td>';
    }
    $out .= '</tr><tr>';
    foreach ($images as $id => $image) {
      if ($image)
        $out .= '<td align="center" style="border: 0px;"><input type="submit" name="euhwc_photo_competition_delete_'.$category.'_'.$id.'" value="Delete" onclick="if(confirm(\'Are you sure you want to delete this photo?\')) return true; return false;"/></td>';
      else
        $out .= '<td align="center" style="border: 0px;"><input type="file" name="euhwc_photo_competition_file_'.$category.'_'.$id.'" id="euhwc_photo_competition_file_'.$category.'_'.$id.'"/></td>';
    }
    $out .= '</tr><tr>';
    $out .= '<td colspan="3" align="right" style="border: 0px;"><input type="submit" id="euhwc_photo_competition_submit" name="euhwc_photo_competition_submit" value="Save"/></td>';
    $out .= '</tr>';
    $out .= '</table>';
  }

  $out .= '</form>';

  return $out;
}

function euhwc_photo_competition_entries_shortcode() {
  $categories = unserialize(PHOTOCOMP_CATEGORIES);
  $data = euhwc_photo_competition_get_all_entries($categories);

  $out = '';

  $total_count = 0;
  foreach ($categories as $category => $value) {
    $count = 0;
    foreach ($data as $user_id => $udata) {
      foreach ($udata as $category2 => $cdata) {
        if ($category == $category2) {
          foreach ($cdata['images'] as $image => $idata) {
            if ($idata['thumb'])
              $count++;
          }
        }
      }
    }
    $total_count += $count;
    $out .= $category . ' has ' . $count . ' entries <br/>';
  }
  $out .= $total_count . ' total entries <br/><br/>';

  $by_cat = array();
  foreach ($categories as $cat => $ignore)
    $by_cat[$cat] = array();

  foreach ($data as $user_id => $udata) {
    //$out .= $user_id.'<br/>';
    foreach ($udata as $category => $cdata) {
      //$out .= '&nbsp;&nbsp;&nbsp;'.$category.'<br/>';
      foreach ($cdata['images'] as $image => $idata) {
        if ($idata['thumb']) {
          //$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.count($idata['votes']).' <a href="'.$idata['full'].'"><img style="display: inline;" src="'.$idata['thumb'].'" width="250px"/></a>';
          array_push($by_cat[$category], $idata);
        }
      }
      //$out .= '<br/>';
    }
  }

  foreach ($by_cat as $category => $images) {
    $out .= '<h3>'.$category.'</h3>';
    foreach ($images as $image) {
      if (count($image['votes']) > 0)
        $out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.count($image['votes']).' <a href="'.$image['full'].'"><img style="display: inline;" src="'.$image['thumb'].'" width="250px"/></a><br/>';
    }
  }

  return $out;
}

function euhwc_photo_competition_voting_shortcode() {
  if (!is_user_logged_in()) {
    wp_login_form();
    return '';
  }

  global $current_user;

  $messages = array();
  $out = '';

  $category_id = get_query_var('category');

  if (isset($_POST['euhwc_photo_competition_form_vote_submitted'])) {
    if (wp_verify_nonce($_POST['euhwc_photo_competition_form_vote_submitted'], 'euhwc_photo_competition_form_vote')) {
      $votes = array();
      if (isset($_POST['euhwc_photo_competition_vote']))
        $votes = $_POST['euhwc_photo_competition_vote'];
      $result = euhwc_photo_competition_vote_photo($current_user->ID, $category_id, $votes);
      if ($result !== true) {
        $out .= '<div class="error">There was a problem saving your vote. '.$result.'</div>';
      } else {
        $out .= '<div class="success">Your vote has been saved.</div>';
      }
      $category_id = FALSE;
    } else {
      $out .= '<div class="error">There was a problem saving your vote. Nonce check failed.</div>';
    }
  }

  $categories = unserialize(PHOTOCOMP_CATEGORIES);
  $data = euhwc_photo_competition_get_all_entries($categories);

  if (!$category_id) {

    foreach ($categories as $category => $value) {

      $votes = array();
      foreach ($data as $user_id => $udata) {
        foreach ($udata as $category2 => $cdata) {
          if ($category == $category2) {
            foreach ($cdata['images'] as $image => $idata) {
              if (is_array($idata['votes']) && in_array($current_user->ID, $idata['votes']))
                $votes[] = $idata;
            }
          }
        }
      }

      $out .= '<h2 style="display: inline; margin-right: 1em;">' . $value['title'] . '</h2>';
      $out .= $value['description'];

      $out .= '<table><tr>';
      $out .= '<td colspan="2">';
      $out .= 'You have voted for <b>'.sizeof($votes).' out of 2</b> photos in this category. ';
      $out .= '<b><a href="?category='.$category.'">'.(sizeof($votes) == 0 ? 'Place' : 'Update').' my Vote</a></b>';
      $out .= '</td>';
      $out .= '</tr><tr>';

      foreach ($votes as $image) {
        $out .= '<td align="center"><a href="' . $image['full'] . '" class="thickbox"><img src="' . $image['thumb'] . '" style="border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;" /></a></td>';
      }
      for ($i = sizeof($votes); $i < 2; $i++) {
        $out .= '<td align="center"><img src="' . plugins_url( 'placeholder.png' , __FILE__ ) . '" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;"/></td>';
      }

      $out .= '</tr></table>';
    }
  } else {

    $category = $categories[$category_id];
    $out .= '<h2 style="display: inline; margin-right: 1em;">' . $category['title'] . '</h2>';
    $out .= $category['description'];

    $photos = array();
    foreach ($data as $user_id => $udata) {
      foreach ($udata as $category => $cdata) {
        if ($category == $category_id) {
          foreach ($cdata['images'] as $image => $idata) {
            if ($idata['thumb'])
              $photos[] = $idata;
          }
        }
      }
    }

    $out .= "<script>
function check_votes() {
  count = 0;
  votes = document.getElementById('euhwc_photo_competition_vote_form').elements['euhwc_photo_competition_vote[]'];
  for (x=0; x<votes.length; x++){
    if (votes[x].checked) {
      count++;
    }
  }
  if (count > 2) {
    alert('You can vote for a maximum of 2 photos. Please uncheck some photos.');
    return false;
  } else {
    return true;
  }
}
</script>";

    $out .= '<form method="post" action="?" id="euhwc_photo_competition_vote_form" onsubmit="return check_votes()">';
    $out .= wp_nonce_field('euhwc_photo_competition_form_vote', 'euhwc_photo_competition_form_vote_submitted');
    $out .= '<p>To vote, select two photos below and then click save. You can view a larger version of each image by clicking on it.</p>';

    $out .= '<p><input type="submit" name="submit" value="Save my Vote"/></p>';
    $out .= '<input type="hidden" name="category" value="'.$category_id.'">';

    $out .= '<table><tr>';

    // Shuffle the photos into a stable random order
    srand(42);
    $order = array();
    $i = 0;
    foreach ($photos as $photo) {
      array_push($order, ($i * 3824624) % 200);
      $i++;
    }
    //$order = array_map(create_function('$val', 'return mt_rand();'), range(1, count($photos)));
    //$out .= print_r($order, true);
    array_multisort($order, $photos);

    $i = 1;
    foreach ($photos as $id => $photo) {
      $checked = '';
      $background = '#fff';
      if (in_array($current_user->ID, $photo['votes'])) {
        $checked = ' checked="checked"';
        $background = '#8f8';
      }

      $out .= '<td align="center" id="euhwc_photo_competition_photo_td_'.$id.'" style="text-align: center; background-color: '.$background.';">'."\n";
      $out .= '<a href="'.$photo['full'].'" class="thickbox">';
      $out .= '<img src="'.$photo['thumb'].'" style=" border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;"/>'."\n";
      $out .= '</a>';
      $out .= '<br/><input type="checkbox"'.$checked.' name="euhwc_photo_competition_vote[]" value="'.$photo['post_id'].'" id="euhwc_photo_competition_photo_'.$id.'" onclick="document.getElementById(\'euhwc_photo_competition_photo_td_'.$id.'\').style.backgroundColor = (document.getElementById(\'euhwc_photo_competition_photo_'.$id.'\').checked ? \'#8f8\' : \'#fff\');"/>';
      $out .= ' <label for="euhwc_photo_competition_photo_'.$id.'">Choose this photo</label>';
      $out .= '</td>'."\n";
      if ($i % 3 == 0)
        $out .= '</tr><tr style="border-top: 1px solid #777;">'."\n";
      $i++;
    }

    $out .= '</tr></table></form>';
  }
  return $out;
}

function euhwc_cmp_voting($a, $b) {
  if ($a['votes'] == $b['votes'])
    return 0;
  return ($a['votes'] < $b['votes']) ? -1 : 1;
}

function euhwc_results_html($image, $prefix, $suffix, $size) {
  return '<td align="center">'.$prefix.$image['name'].$suffix.'<br/><a href="' . $image['full'] . '" class="thickbox"><img src="' . $image['thumb'] . '" width="'.$size.'"style="border-style: solid; border-width: 1px; border-color: #777; margin: 0.3em;" /></a></td>';
}

function euhwc_photo_competition_results_shortcode() {
  $categories = unserialize(PHOTOCOMP_CATEGORIES);
  $data = euhwc_photo_competition_get_all_entries($categories);

  $out = '';

  $by_cat = array();
  foreach ($categories as $cat => $ignore)
    $by_cat[$cat] = array();

  foreach ($data as $user_id => $udata) {
    foreach ($udata as $category => $cdata) {
      foreach ($cdata['images'] as $image => $idata) {
        if ($idata['thumb']) {
          array_push($by_cat[$category], $idata);
        }
      }
    }
  }

  foreach ($by_cat as $category => $images) {
    usort($images, "euhwc_cmp_voting");
    $winner = $images[count($images)-1];
    $second = $images[count($images)-2];
    $third = $images[count($images)-3];

    $out .= '<h2 style="display: inline; margin-right: 1em;">' . $categories[$category]['title'] . '</h2>';
    $out .= $categories[$category]['description'];

    $out .= '<table><tr>';

    $out .= euhwc_results_html ($winner, 'Winner: <b>', '</b>', 250);
    $out .= euhwc_results_html ($second, 'Runner up: ', '', 150);
    $out .= euhwc_results_html ($third, 'Runner up: ', '', 150);

    $out .= '</tr></table>';
  }

  return $out;
}

function euhwc_photo_competition_vote_photo($user_id, $category, $image_ids) {
  // clear all the users votes
  $args = array(
    'post_type' => 'euhwc_photocom_entry',
    'post_status' => 'publish',
    'nopaging' => TRUE
  );
  $images = new WP_Query($args);
  foreach ($images->posts as $image) {
    $photo_category = get_post_meta($image->ID, 'category_id', true);
    if ($category == $photo_category)
      delete_post_meta($image->ID, 'photo_competition_vote', $user_id);
  }
  // add the new votes
  foreach ($image_ids as $image_id) {
    $result = add_post_meta($image_id, 'photo_competition_vote', $user_id);
    if (!$result) {
      return 'Failed to save your vote ' . $result . ' ' . $image_id . ' ' . $user_id;
    }
  }
  return true;
}

function euhwc_photo_competition_save_photo($category, $id, $file) {
  $error = FALSE;

  $image_data = getimagesize($file['tmp_name']);
  if ($file['error']) {
    $error = 'There was an error uploading your file!';
  } elseif (!in_array($image_data['mime'], unserialize(PHOTOCOMP_TYPE_WHITELIST))) {
    $error = 'Your photo must be a jpeg, png or gif.';
  } elseif(($file['size'] > PHOTOCOMP_MAX_UPLOAD_SIZE)) {
    $error = 'Your image was too large. It can be at most 10MB.';
  }

  if ($error)
    return array('error' => $error);

  $result['title'] = $file['name'];

  global $current_user;
  $image_data = array(
    'post_title' => $result['title'],
    'post_status' => 'publish',
    'post_author' => $current_user->ID,
    'post_type' => 'euhwc_photocom_entry'
  );

  if ($post_id = wp_insert_post($image_data)) {
    $ret = euhwc_photo_competition_process_image($post_id, $file, $category, $id);
    if ($ret !== TRUE)
      return $ret;
  }

  return TRUE;
}

function euhwc_photo_competition_process_image($post_id, $file, $category, $id) {
  $result = euhwc_photo_competition_upload_image($post_id, $file);
  if ($result !== TRUE)
    return $result;

  $pathinfo = pathinfo($file['name']);
  $ext = $pathinfo['extension'];

  update_post_meta($post_id, 'category_id', $category);
  update_post_meta($post_id, 'image_id', $id);
  update_post_meta($post_id, 'photo_url', $post_id.'.'.$ext);
  update_post_meta($post_id, 'photo_thumbnail_url', $post_id.'_thumbnail.'.$ext);
  return TRUE;
}

// Handle the file upload
function euhwc_photo_competition_upload_image($post_id, $file) {
  //$upload_errors = array(
  //  UPLOAD_ERR_INI_SIZE   => "Larger than upload_max_filesize.",
  //  UPLOAD_ERR_FORM_SIZE  => "Larger than form MAX_FILE_SIZE.",
  //  UPLOAD_ERR_PARTIAL    => "Partial upload.",
  //  UPLOAD_ERR_NO_FILE    => "No file.",
  //  UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
  //  UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
  //  UPLOAD_ERR_EXTENSION  => "File upload stopped by extension.",
  //  UPLOAD_ERR_EMPTY      => "File is empty."
  //);
  //
  //if ($file['error'] > 0) {
  //  return 'Internal error: '.$upload_errors[$file['error']];
  //}

  // Check it's an image
  if (!preg_match('/^image\/.+/', $file['type'])) {
    return 'File is not an image.';
  }

  // Generate file paths
  $pathinfo = pathinfo($file['name']);
  $ext = $pathinfo['extension'];
  $filename = $post_id.'.'.$ext;
  $thumbname = $post_id.'_thumbnail.'.$ext;
  $tmpdir = '/data/sites/euhwc/tmp';

  // Move the photo to the tmp location
  if (!is_writable($tmpdir))
    return 'Destination dir is not writable';
  if (!move_uploaded_file($file['tmp_name'], $tmpdir.'/'.$filename))
    return 'Error moving file';

  // Create a small thumbnail (thumb_path)
  if (!euhwc_photo_competition_create_thumbnail($tmpdir.'/'.$filename, $file['type'], $tmpdir.'/'.$thumbname, 250, 200, 50))
     return 'Failed to generate thumbnail';

  // Move original photo to AWS S3
  $client = S3Client::factory(array(
    'key'    => 'AKIAJCXZW6ACDALH6RHA',
    'secret' => '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',
    'region' => 'eu-west-1',
  ));

  $result = $client->putObject(array(
    'Bucket'     => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'        => $filename,
    'SourceFile' => $tmpdir.'/'.$filename,
  ));
  $result = $client->putObject(array(
    'Bucket'     => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'        => $thumbname,
    'SourceFile' => $tmpdir.'/'.$thumbname,
  ));

  $client->waitUntilObjectExists(array(
    'Bucket' => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'    => $filename
  ));
  $client->waitUntilObjectExists(array(
    'Bucket' => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'    => $thumbname
  ));

  unlink($tmpdir.'/'.$filename);
  unlink($tmpdir.'/'.$thumbname);

  return TRUE;
}

function euhwc_photo_competition_delete_photo($user_id, $category, $id) {

  $args = array(
    'author' => $user_id,
    'post_type' => 'euhwc_photocom_entry',
    'post_status' => 'publish',
    'nopaging' => TRUE
  );

  $photos = new WP_Query($args);

  $post_id = FALSE;
  if ($photos->post_count) {
    foreach ($photos->posts as $photo) {
      $photo_category = get_post_meta($photo->ID, 'category_id', true);
      $photo_id = get_post_meta($photo->ID, 'image_id', true);
      if ($photo_category == $category && $photo_id == $id) {
        $post_id = $photo->ID;
        break;
      }
    }
  }
  if ($post_id === FALSE) {
    $result['error'] = 'Photo not found!';
    return $result;
  }

  $full = get_post_meta($post_id, 'photo_url', true);
  $thumb = get_post_meta($post_id, 'photo_thumbnail_url', true);

  $client = S3Client::factory(array(
    'key'    => 'AKIAJCXZW6ACDALH6RHA',
    'secret' => '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',
    'region' => 'eu-west-1',
  ));

  $result = $client->deleteObject(array(
    'Bucket'     => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'        => $full,
  ));
  $result = $client->deleteObject(array(
    'Bucket'     => 'photos.euhwc.eusu.ed.ac.uk',
    'Key'        => $thumb,
  ));

  wp_delete_post($post_id, true);
  return TRUE;
}

function euhwc_photo_competition_create_thumbnail($orig_path, $type, $new_path, $width, $height, $quality) {
  if (preg_match('/jpg|jpeg/', $type))
    $src_img = imagecreatefromjpeg($orig_path);
  else if (preg_match('/png/', $type))
    $src_img = imagecreatefrompng($orig_path);
  else
    return FALSE;

  $orig_width = imagesx($src_img);
  $orig_height = imagesy($src_img);

  if (!$orig_width || !$orig_height)
    return FALSE;

  if ($orig_width > $orig_height) {
    $thumb_width = $width;
    $thumb_height = $width/$orig_width * $orig_height;
  } else {
    $thumb_width = $height/$orig_height * $orig_width;
    $thumb_height = $height;
  }

  $dst_img = imagecreatetruecolor($thumb_width, $thumb_height);
  if (!$dst_img)
    return FALSE;

  if (!imagecopyresampled($dst_img, $src_img, 0,0,0,0, $thumb_width, $thumb_height, $orig_width, $orig_height))
    return FALSE;

  if (preg_match('/png/', $type)) {
    if (!imagepng($dst_img, $new_path))
      return FALSE;
  } else {
    if (!imagejpeg($dst_img, $new_path, $quality))
      return FALSE;
  }

  if (!imagedestroy($dst_img))
    return FALSE;
  if (!imagedestroy($src_img))
    return FALSE;

  return TRUE;
}

?>
