<?php
/*
Plugin Name: Substitute Displayname
Description: Replaces the default display name with the real name for newly registered users.
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

function substitute_displayname_user_register($user_id) {
  $info = get_userdata($user_id);
  $args = array(
    'ID' => $user_id,
    'display_name' => $info->first_name . ' ' . $info->last_name
  );
  wp_update_user($args);
}

add_action('user_register', 'substitute_displayname_user_register', 99);

?>
