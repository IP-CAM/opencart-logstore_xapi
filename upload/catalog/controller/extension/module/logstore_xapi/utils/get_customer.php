<?php
  function get_customer($customer_id, $general) {

    // get the customer from the DB
    $customer_row = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id='" . $general['db']->escape($customer_id) . "'")->row;

    if(!$customer_row) return;

    return [
      "name" => trim($customer_row['firstname'] . ' ' . $customer_row['lastname']),
      "mbox" => "mailto:" . $customer_row['email'],
    ];
  }
?>