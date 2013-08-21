<?php

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

?>