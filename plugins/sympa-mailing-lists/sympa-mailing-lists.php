<?php
/*
Plugin Name: Sympa Mailing Lists
Description: Provides Sympa mailing list management, including a shortcode to display a form for users to subscribe/unsubscribe from mailing lists.
Version: 1.0
Author: Alex Collins
*/

// Add query var for tickets
function sympa_form_query_vars($public_query_vars) {
  $public_query_vars[] = 'ticket';
  return $public_query_vars;
}
add_filter('query_vars', 'sympa_form_query_vars');

class Request {
  var $email, $command, $lists;

  function __construct($email, $command, $lists) {
    $this->email = $email;
    $this->command = $command;
    $this->lists = $lists;
    if ($this->lists == null)
      $this->lists = array();
  }

  /** @return True if the request fields are correctly formatted. */
  function valid() {
    $valid_email = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $this->email);
    $valid_command = in_array($this->command, array('subscribe', 'unsubscribe'));
    $valid_lists = (count($this->lists) > 0);
    //foreach ($this->lists as $list) {
    //  if (!in_array($list, array('hillwalkers', 'vac-hillwalk'))) {
    //    $valid_lists = FALSE;
    //  }
    //}
    return $valid_email && $valid_command && $valid_lists;
  }
}

function sympa_form_current_page_url() {
  $url = 'http';
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    $url .= 's';
  $url .= '://' . $_SERVER['SERVER_NAME'];
  if ($_SERVER['SERVER_PORT'] != '80')
    $url .= ':'.$_SERVER['SERVER_PORT'];
  $url .= $_SERVER['REQUEST_URI'];
  return $url;
}

function sympa_form_send_confirmation_mail($request, $ticket) {
  $subject = 'EUHWC Mailing List Subscription';
  $message = <<<EOD
You recently requested to be %command% %lists%.
To complete your request, please follow this link:

%link%

If you did not make this request you can safely ignore this email.

Thanks,
EUHWC
EOD;

  if ($request->command == 'subscribe')
    $command = 'subscribed to';
  else
    $command = 'unsubscribed from';
  $lists = implode(', ', $request->lists);

  $url = parse_url(sympa_form_current_page_url());
  parse_str($url['query'], $query);
  $query['ticket'] = $ticket;
  $link = $url['scheme'].'://'.$url['host'].$url['path'] . '?' . http_build_query($query);

  $message = str_replace('%lists%', $lists,
             str_replace('%command%', $command,
             str_replace('%link%', $link, $message)));

  $from = 'From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>';

  $headers[] = $from;
  return wp_mail($request->email, $subject, $message, $headers);
}

function sympa_form_mailing_list_command($request) {
  foreach ($request->lists as $id => $list) {
    $list = explode('@', $list);
    if ($request->command == 'subscribe')
      $commands[] = 'QUIET ADD ' . $list[0] . ' ' . $request->email;
    else
      $commands[] = 'QUIET DELETE ' . $list[0] . ' ' . $request->email;
  }
  $commands[] = 'QUIT';
  $to = array('sympa@mlist.is.ed.ac.uk', get_option('admin_email'));
  $from = 'From: ' . get_option('admin_email');
  $subject = '';
  $message = implode("\n", $commands);

  $headers[] = $from;
  return wp_mail($to, $subject, $message, $headers);
}

// Generates HTML for a form
function sympa_form($lists, $request = null) {
  $result = '';

  global $user_email;
  get_currentuserinfo();

  $email = isset($request) ? $request->email : $user_email;
  $command = isset($request) ? $request->command : 'subscribe';
  $req_lists = isset($request) ? $request->lists : array($lists[0][0] => $lists[0][2]);

  $result .= '<form method="post" action="">'
  . '<table>'
  . '<tr><td>Email address:</td><td><input type="text" size="32" maxlength="250" name="sympa_form_email" value="' . $email . '"/></td></tr>'
  . '<tr><td>&nbsp;</td><td>'
  . '    <input type="radio" name="sympa_form_command" value="subscribe" id="command.subscribe"' . ($command == 'subscribe' ? ' checked="checked"' : '') . ' />'
  . '    <label for="command.subscribe">Subscribe</label>'
  . '    <input type="radio" name="sympa_form_command" value="unsubscribe" id="command.unsubscribe"' . ($command == 'unsubscribe' ? ' checked="checked"' : '') . ' />'
  . '    <label for="command.unsubscribe">Unsubscribe</label>'
  . '</td></tr>'
  . '<tr><td>Mailing lists:</td><td>';

  foreach ($lists as $list) {
    $id = $list[0];
    $address = $list[2];
    $name = $list[1];
    $result .= '<input type="checkbox" name="sympa_form_lists[' . $id . ']" value="true" id="lists.' . $id . '"' . (array_key_exists($id, $req_lists) ? ' checked="checked"' : '') . ' />'
            . '<label for="lists.' . $id . '">' . $name . ' (<i>' . $address . '</i>)</label><br />';
  }

  $result .= '</td></tr>'
  . '<tr><td>&nbsp;</td><td><input type="submit" value="Submit" name="sympa_form_submit"></td></tr>'
  . '</table>'
  . '</form>';

  return $result;
}

// Shortcode for sympa forms
function sympa_form_shortcode( $atts, $content = null ) {

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
    return sympa_form($lists);

  if (isset($_POST['sympa_form_submit'])) {
    // Decode post data
    $email = $_POST['sympa_form_email'];
    $command = $_POST['sympa_form_command'];
    if (is_array($_POST['sympa_form_lists']))
      $list_ids = array_keys($_POST['sympa_form_lists']);
    else
      $list_ids = array();

    // Construct request object
    $req_lists = array();
    foreach ($list_ids as $id) {
      $req_lists[$id] = $lists_dict[$id][1];
    }
    $request = new Request($email, $command, $req_lists);

    if ($request->valid()) {
      // Send confirmation email
      $ticket = wp_create_nonce('sympa_form' . $email . current_time('timestamp') );
      if (sympa_form_send_confirmation_mail($request, $ticket)) {
        set_transient('sympa_form_' . $ticket, $request, $timeout);
        return '<p class="success">An email has been sent to your address with a confirmation link.</p>';
      } else {
        return '<p class="error">Failed to send confirmation email!</p>';
      }
    } else {
      // Request was not valid
      return '<p class="error">The details you entered are not valid. Please check them and try again.</p>'.sympa_form($lists, $request);
    }
  }

  if ($ticket != '') {
    // Validate ticket
    $request = get_transient('sympa_form_' . $ticket);
    if ($request === false) {
      return '<p class="error">The link has expired. Please submit the form to obtain a new link.</p>' . sympa_form($lists);
    } else {
      // Remove the ticket
      delete_transient('sympa_form_' . $ticket);
      // Add to mailing list(s)
      if (sympa_form_mailing_list_command($request))
        return '<p class="success"><span class="nowrap">' . $request->email . '</span> has been successfully ' . $request->command . 'd to <span class="nowrap">' . implode(', ', $request->lists) . '</span>.</p>';
      else
        return '<p class="error">Failed to process request!</p>';
    }
  }

}

add_shortcode('sympaform', 'sympa_form_shortcode');

?>
