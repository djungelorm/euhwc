<?php
/*
Plugin Name: EUHWC Photo Competition
Description: Provides functionality to run a photo competition.
Version: 2.1
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
Plugin URI: http://euhwc.eusu.ed.ac.uk/wp-content/plugins/euhwc-photo-competition/readme.txt
*/
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

require_once(dirname(__FILE__).'/includes/options.php');
require_once(dirname(__FILE__).'/includes/photo.php');
require_once(dirname(__FILE__).'/includes/photos.php');
require_once(dirname(__FILE__).'/includes/category.php');
require_once(dirname(__FILE__).'/includes/categories.php');
require_once(dirname(__FILE__).'/includes/admin.php');

require_once(dirname(__FILE__).'/shortcodes/upload.php');
require_once(dirname(__FILE__).'/shortcodes/voting.php');
require_once(dirname(__FILE__).'/shortcodes/results.php');

?>
