<?php
  function get_course($order_product_row, $general) {

    // get the moodle course id from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    if(!$product_moodle_mapping_row) return;

    return [
      "id" => "https://learn.biblemesh.com/course/view.php?id=" . $product_moodle_mapping_row['moodle_course_id'],
      "definition" => [
        "type" => "http://id.tincanapi.com/activitytype/lms/course",
        "name" => [
          "en" => $order_product_row['name'],
        ],
        "moreInfo" => "https://sandbox.biblemesh.com/index.php?route=product/product&product_id=" . $order_product_row['product_id'],
      ],
    ];
  }
?>