<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

/**
 * Generate form for managing a mailing list subscription
 */
function sympa_mailing_lists_form($lists, $request = null) {
  $result = '';

  global $user_email;
  get_currentuserinfo();

  $email = isset($request) ? $request->email : $user_email;
  $command = isset($request) ? $request->command : 'subscribe';
  $req_lists = isset($request) ? $request->lists : array();

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
    $result .= '<input type="checkbox" name="sympa_form_lists[' . $id . ']" value="true" id="lists.' . $id . '"' . (array_key_exists($id, $req_lists) ? ' checked="checked"' : '') . ' /> '
    . '<label for="lists.' . $id . '">' . $name . ' (<i>' . $address . '</i>)</label><br />';
  }

  $result .= '</td></tr>'
  . '<tr><td>&nbsp;</td><td><input type="submit" value="Submit" name="sympa_form_submit"></td></tr>'
  . '</table>'
  . '</form>';

  return $result;
}

?>