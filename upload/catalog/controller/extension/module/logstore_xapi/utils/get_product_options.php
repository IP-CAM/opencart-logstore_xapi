<?php
  function get_product_options($order_row, $order_product_row, $general) {

    $product_condition_minus_table = ".product_id='" . $general['db']->escape($order_product_row['product_id']) . "'";

    // get order options from the DB
    $order_option_rows = $general['db']->query(
      "SELECT po.option_id, pov.option_value_id, oo.name, oo.value, povtmp.unit, povtmp.interval " .
      "FROM `" . DB_PREFIX . "order_option` as oo " .
      "LEFT JOIN `" . DB_PREFIX . "product_option` as po " .
        "ON (po.product_option_id=oo.product_option_id AND po" . $product_condition_minus_table . ") " .
      "LEFT JOIN `" . DB_PREFIX . "product_option_value` as pov " .
        "ON (pov.product_option_value_id=oo.product_option_value_id AND pov" . $product_condition_minus_table . ") " .
      "LEFT JOIN `" . DB_PREFIX . "product_option_value_to_moodle_period` as povtmp " .
        "ON (povtmp.product_option_value_id=oo.product_option_value_id AND povtmp" . $product_condition_minus_table . ") " .
      "WHERE oo.order_id='" . $general['db']->escape($order_row['order_id']) . "' AND povtmp" . $product_condition_minus_table
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

      $optionType = $order_option_row['name'] . ' [id:' . $order_option_row['option_id'] . ']';
      $optionValue = $order_option_row['value'] . ' [id:' . $order_option_row['option_value_id'] . ']';

      $options[] = array_merge(
        [
          "name" => $optionType,
          "value" => $optionValue,
        ],
        isset($expiretime)
          ? [
            "expiration" => date('c', $expiretime),
          ]
          : []
      );

    }

    if(isset($order_row['custom_field'])) {
      $custom_field = json_decode($order_row['custom_field'], TRUE);

      if(
        is_array($custom_field)
        && isset($custom_field['import_info'])
        && isset($custom_field['import_info']['products'])
        && isset($custom_field['import_info']['products'][$order_product_row['product_id']])
      ) {
        $options[] = $custom_field['import_info']['products'][$order_product_row['product_id']];
      }
    }

    if(count($options) === 0) {
      return [];
    }
    
    return [
      "http://lrs.resourcingeducation.com/extension/product-options" => $options,
    ];

  }
?>