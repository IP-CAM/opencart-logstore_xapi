<?php
  require_once('get_expiration.php');

  function get_course($order_row, $order_product_row, $general) {

    // get the moodle course id from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    if(!$product_moodle_mapping_row) return;

    $isRecurring = false;

    return [
      "id" => mb_ereg_replace('MOODLE_ID', $product_moodle_mapping_row['moodle_course_id'], $general['moodle_url_template']),
      "definition" => [
        "type" => "http://id.tincanapi.com/activitytype/lms/course",
        "name" => [
          $general['config_language'] => $order_product_row['name'],
        ],
        "moreInfo" => $general['site_base'] . "index.php?route=product/product&product_id=" . $order_product_row['product_id'],
        "extensions" => [
          "http://lrs.learninglocker.net/define/extensions/info" => array_merge(
            [
              "price" => $order_product_row['price'],
            ],
            get_expiration($order_product_row, $general),
            ($isRecurring
              ? [
                "recurringInfo" => [
                  "id" => 123,
                  "period" => "??",
                ]
              ]
              : array()
            )
          ),
        ],
      ],
    ];
  }
?>