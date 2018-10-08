<?php
  function get_customer($customer_id, $general) {

    // get the customer from the DB
    $customer_row = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id='" . $general['db']->escape($customer_id) . "'")->row;

    if(!$customer_row) return;

    $fullname = trim($customer_row['firstname'] . ' ' . $customer_row['lastname']);
    $hasvalidemail = mb_ereg_match("[A-Z0-9\\.\\`\\'_%+-]+@[A-Z0-9.-]+\\.[A-Z]{1,63}$", $customer_row['email'], "i");

    if ($hasvalidemail) {
      return [
        "name" => $fullname,
        "mbox" => "mailto:" . $customer_row['email'],
      ];
    }

    return [
      'name' => $fullname,
      'account' => [
          'homePage' => $general['site_base'],
          'name' => strval($customer_row['customer_id']),
      ],
    ];
  }
?>