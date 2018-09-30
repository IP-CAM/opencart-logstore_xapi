<?php
  function get_order_options($order_row, $order_product_row, $general) {

    $product_condition = "povtmp.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'";

    // get order options from the DB
    $order_option_rows = $general['db']->query(
      "SELECT oo.product_option_id, oo.product_option_value_id, oo.name, oo.value, povtmp.unit, povtmp.interval " .
      "FROM `" . DB_PREFIX . "order_option` as oo " .
      // "LEFT JOIN `" . DB_PREFIX . "product_option_value` as pov " .
      //   "ON (pov.product_option_value_id=oo.product_option_value_id) " .
      "LEFT JOIN `" . DB_PREFIX . "product_option_value_to_moodle_period` as povtmp " .
        "ON (povtmp.product_option_value_id=oo.product_option_value_id AND " . $product_condition . ") " .
      "WHERE oo.order_id='" . $general['db']->escape($order_row['order_id']) . "' AND " . $product_condition
    )->rows;


    // "AND pov.option_id IN ('13', '14')"



    $options = array();

    foreach($order_option_rows as $order_option_row) {

      switch($order_option_row['unit']) {
        case 'day':
        case 'month':
        case 'year':
          $expiretime = strtotime(date('Y-m-d') . ' + ' . $order_option_row['interval'] . ' ' . $order_option_row['unit'] . 's');
          break;
      }

      $optionType = $order_option_row['name'] . ' [' . $order_option_row['product_option_id'] . ']';
      $optionValue = $order_option_row['value'] . ' [' . $order_option_row['product_option_value_id'] . ']';

      $options[] = array_merge(
        [
          $optionType => $optionValue,
        ],
        isset($expiretime)
          ? [
            "expiration" => date('c', $expiretime),
          ]
          : []
      );

    }

    if(count($options) === 0) {
      return [];
    }
    
    return [
      "http://lrs.resourcingeducation.com/extension/product-options" => $options,
    ];

  }
?>