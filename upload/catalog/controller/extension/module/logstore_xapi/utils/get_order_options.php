<?php
  function get_order_options($order_row, $order_product_row, $general) {

    // get order options from the DB
    $order_option_rows = $general['db']->query(
      "SELECT oo.product_option_id, oo.product_option_value_id, oo.name, oo.value FROM `" . DB_PREFIX . "order_option` as oo " .
      "WHERE oo.order_id='" . $general['db']->escape($order_row['order_id']) . "' " .
        "AND oo.order_product_id='" . $general['db']->escape($order_product_row['order_product_id']) . "'"
    )->rows;

    $options = array();

    foreach($order_option_rows as $order_option_row) {
      $options[$order_option_row['name'] . ' [' . $order_option_row['product_option_id'] . ']'] =
        $order_option_row['value'] . ' [' . $order_option_row['product_option_value_id'] . ']';
    }

    return $options;
  }
?>