<?php
  function get_partner($order_product_row, $general) {

    $product_condition = "p.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'";

    // get the partner (i.e. manufacturer) info from the DB
    $manufacturer_row = $general['db']->query(
      "SELECT m.manufacturer_id, m.name, ua.keyword FROM `" . DB_PREFIX . "manufacturer` as m" .
      "LEFT JOIN `" . DB_PREFIX . "product` as p ON (p.manufacturer_id=m.manufacturer AND " . $product_condition . ") " .
      "LEFT JOIN `" . DB_PREFIX . "url_alias` as ua ON (ua.query=CONCAT('manufacturer_id=',m.manufacturer_id)) " .
      "WHERE " . $product_condition
    )->row;

    if(!$manufacturer_row) return [];

    $keyword = isset($manufacturer_row['keyword']) ? $manufacturer_row['keyword'] : "product/manufacturer/info?manufacturer_id=" . $manufacturer_row['id'];

    return [[
      "id" => $general['site_base'] . $keyword,
      "definition" => [
        // "type" => "http://id.tincanapi.com/activitytype/organization",
        // "type" => "http://id.tincanapi.com/activitytype/section",
        "type" => "http://activitystrea.ms/schema/1.0/organization",
        "name" => [
          $general['config_language'] => $manufacturer_row['name'],
        ],
      ],
      "objectType" => "Activity"
    ]];

  }
?>