<?php
  function get_user($user_id, $general) {

    // get the user from the DB
    $user_row = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id='" . $general['db']->escape($user_id) . "'")->row;

    if(!$user_row) return;

    return [
      "name" => trim($user_row['firstname'] . ' ' . $user_row['lastname']),
      "mbox" => "mailto:" . $user_row['email'],
    ];
  }
?>