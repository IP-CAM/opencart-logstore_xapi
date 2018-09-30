<?php
  function get_institution($order_row, $general) {

    // order > customer_group_description: id, name (import for institution)

    // get the institution (i.e. customer_group) info from the DB
    $customer_group_description_row = $general['db']->query(
      "SELECT cgd.id, cgd.name FROM `" . DB_PREFIX . "customer_group_description` as cgd" .
      "WHERE cgd.customer_group_id='" . $general['db']->escape($order_row['customer_group_id']) . "'"
    )->row;

    if(!$customer_group_description_row) return [];

    return [[
      "id" => $general['site_base'] . "customer/group?customer_group_id=" . $customer_group_description_row['id'],
      "definition" => [
        // "type" => "http://activitystrea.ms/schema/1.0/organization",
        // "type" => "http://id.tincanapi.com/activitytype/section",
        "type" => "http://id.tincanapi.com/activitytype/organization",
        "name" => [
          $general['config_language'] => $customer_group_description_row['name'],
        ],
      ],
      "objectType" => "Activity"
    ]];

  }
?>