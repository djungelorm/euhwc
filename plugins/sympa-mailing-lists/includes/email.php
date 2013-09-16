<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

/**
 * Get an absolute URL to the current page, without query arguments
 */
function sympa_mailing_lists_current_page_url() {
  $url = 'http';
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    $url .= 's';
  $url .= '://' . $_SERVER['SERVER_NAME'];
  if ($_SERVER['SERVER_PORT'] != '80')
    $url .= ':'.$_SERVER['SERVER_PORT'];
  $url .= $_SERVER['REQUEST_URI'];
  return $url;
}

/**
 * Send an email to verify the email address for a request
 */
function sympa_mailing_lists_send_confirmation_mail($request, $ticket) {
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

  $url = parse_url(sympa_mailing_lists_current_page_url());
  if (isset($url['query']))
    parse_str($url['query'], $query);
  else
    $query = array();
  $query['ticket'] = $ticket;
  $link = $url['scheme'].'://'.$url['host'].$url['path'] . '?' . http_build_query($query);

  $message = str_replace('%lists%', $lists,
             str_replace('%command%', $command,
             str_replace('%link%', $link, $message)));

  $from = 'From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>';

  $headers[] = $from;
  return wp_mail($request->email, $subject, $message, $headers);
}

/**
 * Execute a mailing list command via email
 */
function sympa_mailing_lists_send_list_command($request) {
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

?>