<?php

  // Ideally, this would only include coupons which are associated with THIS product in the order.
  // That is, multiple products in a course will fire off separate xapi statements and it does not
  // make sense that a product which is unconnected to the coupon would include the coupon in
  // its statement. However, it is sometimes problematic to figure out which product a coupon 
  // relates to, since the precise product does not need to be set up in the coupon definition. Thus,
  // we are simply including the coupon with the statements for all products in an order at this point.

  function get_coupons($order_row, $order_product_row, $general) {

    // get the coupons from the DB
    // $product_condition = "cp.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'";
    $order_condition = "ch.order_id='" . $general['db']->escape($order_row['order_id']) . "'";

    $coupon_rows = $general['db']->query(
      "SELECT c.coupon_id, c.name, c.code " .
      "FROM `" . DB_PREFIX . "coupon` as c " .
      // "LEFT JOIN `" . DB_PREFIX . "coupon_product` as cp ON (cp.coupon_id=c.coupon_id AND " . $product_condition . ") " .
      "LEFT JOIN `" . DB_PREFIX . "coupon_history` as ch ON (ch.coupon_id=c.coupon_id AND " . $order_condition . ") " .
      // "WHERE " . $product_condition . " AND " . $order_condition
      "WHERE " . $order_condition
    )->rows;

    $coupons = array();

    foreach($coupon_rows as $coupon_row) {
      $coupons[] = [
        "coupon_id" => $coupon_row['coupon_id'],
        "coupon_code" => $coupon_row['code'],
        "coupon_name" => $coupon_row['name'],
      ];
    }

    if(count($coupons) === 0) {
      return [];
    }
    
    return [
      "http://lrs.resourcingeducation.com/extension/coupons" => $coupons,
    ];
  }
?>