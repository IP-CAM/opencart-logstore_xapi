<?php
  function get_coupons($order_row, $order_product_row, $general) {

    // get the coupons from the DB
    $product_condition = "cp.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'";
    $order_condition = "ch.order_id='" . $general['db']->escape($order_row['order_id']) . "'";

    $coupon_rows = $general['db']->query(
      "SELECT c.coupon_id, c.name, c.code " .
      "FROM `" . DB_PREFIX . "coupon` as c " .
      "LEFT JOIN `" . DB_PREFIX . "coupon_product` as cp ON (cp.coupon_id=c.coupon_id AND " . $product_condition . ") " .
      "LEFT JOIN `" . DB_PREFIX . "coupon_history` as ch ON (ch.coupon_id=c.coupon_id AND " . $order_condition . ") " .
      "WHERE " . $product_condition . " AND " . $order_condition
    )->rows;

    $coupons = array();

    foreach($coupon_rows as $coupon_row) {
      $coupons[] = [
        "http://lrs.resourcingeducation.com/extension/coupon" => [
          "coupon_id" => $coupon_row['coupon_id'],
          "coupon_code" => $coupon_row['code'],
          "coupon_name" => $coupon_row['name'],
        ]
      ];
    }

    return $coupons;
  }
?>