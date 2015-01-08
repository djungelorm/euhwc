<?php
/*
Plugin Name: EUHWC Logo Competition
Description: Provides shortcodes to run a logo competition.
Version: 1.1
Author: Alex Collins
Author URI: http://www.linkedin.com/in/alexanderjamescollins
License: WTFPL
Plugin URI: http://euhwc.eusu.ed.ac.uk/wp-content/plugins/euhwc-logo-competition/readme.txt
*/
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

defined('ABSPATH') or die('No script kiddies please!');

require_once(dirname(__FILE__).'/includes/logo.php');
require_once(dirname(__FILE__).'/includes/admin.php');
require_once(dirname(__FILE__).'/includes/functions.php');

require_once(dirname(__FILE__).'/shortcodes/submit.php');
require_once(dirname(__FILE__).'/shortcodes/entries.php');
require_once(dirname(__FILE__).'/shortcodes/voting.php');
require_once(dirname(__FILE__).'/shortcodes/results.php');
require_once(dirname(__FILE__).'/shortcodes/winner.php');

?>
