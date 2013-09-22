<?php
/*
Plugin Name: Sympa Mailing Lists
Description: Provides Sympa mailing list management, including a shortcode to display a form for users to subscribe/unsubscribe from mailing lists.
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

require_once(dirname (__FILE__) . '/includes/request.php');
require_once(dirname (__FILE__) . '/includes/email.php');
require_once(dirname (__FILE__) . '/includes/form.php');

/**
 * Add query vars for tickets and direct action links
 */
function sympa_mailing_lists_query_vars($public_query_vars) {
  $public_query_vars[] = 'ticket';
  return $public_query_vars;
}
add_filter('query_vars', 'sympa_mailing_lists_query_vars');

/**
 * Add shortcode to generate a mailing list management page
 */
function sympa_mailing_lists_shortcode( $atts, $content = null ) {

  // Default settings
  $timeout = WEEK_IN_SECONDS;
  $lists = array();
  $lists_dict = array();

  // Decode attributes
  foreach ($atts as $key => $att) {
    if ($key == 'timeout')
      $timeout = $att;
    $att = explode('|', $att);
    array_push($lists, array($key, $att[0], $att[1]));
    $lists_dict[$key] = $att;
  }

  $ticket = get_query_var('ticket');

  if ($ticket == '' && !isset($_POST['sympa_form_submit']))
    return sympa_mailing_lists_form($lists);

  if (isset($_POST['sympa_form_submit'])) {
    // Check post data
    if (!(isset($_POST['sympa_form_email']) &&
          isset($_POST['sympa_form_command']) &&
          isset($_POST['sympa_form_lists'])) &&
          is_array($_POST['sympa_form_lists']))
      return '<p class="error">Invalid request.</p>';

    // Decode post data
    $email = $_POST['sympa_form_email'];
    $command = $_POST['sympa_form_command'];
    $list_ids = array_keys($_POST['sympa_form_lists']);

    // Construct request object
    $req_lists = array();
    foreach ($list_ids as $id) {
      $req_lists[$id] = $lists_dict[$id][1];
    }
    $request = new SympaMailingListsRequest($email, $command, $req_lists);

    // Validate request
    if (!$request->valid()) {
      return '<p class="error">The details you entered are not valid. Please check them and try again.</p>' .
             sympa_mailing_lists_form($lists, $request);
    }

    // Send confirmation email
    $ticket = wp_create_nonce('sympa_form' . $email . current_time('timestamp'));
    if (!sympa_mailing_lists_send_confirmation_mail($request, $ticket)) {
      return '<p class="error">Failed to send confirmation email!</p>';
    }

    set_transient('sympa_form_' . $ticket, $request, $timeout);
    return '<p class="success">An email has been sent to your address with a confirmation link.</p>';
  }

  if ($ticket != '') {
    // Validate ticket
    $request = get_transient('sympa_form_' . $ticket);
    if ($request === false) {
      return '<p class="error">The link has expired. Please submit the form to obtain a new link.</p>' . sympa_mailing_lists_form($lists);
    } else {
      // Remove the ticket
      delete_transient('sympa_form_' . $ticket);
      // Add to mailing list(s)
      if (sympa_mailing_lists_send_list_command($request))
        return '<p class="success"><span class="nowrap">' . $request->email . '</span> has been successfully ' . $request->command . 'd to <span class="nowrap">' . implode(', ', $request->lists) . '</span>.</p>';
      else
        return '<p class="error">Failed to process request!</p>';
    }
  }

}

add_shortcode('sympaform', 'sympa_mailing_lists_shortcode');

?>
