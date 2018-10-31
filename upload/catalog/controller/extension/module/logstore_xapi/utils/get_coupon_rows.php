<?php

  function get_coupon_rows($order_row, $general) {

    $coupon_product_history_subquery = "(SELECT GROUP_CONCAT(cph.product_id SEPARATOR ',') as product_ids FROM `oc_coupon_product_history` as cph WHERE cph.coupon_history_id=ch.coupon_history_id)";

    // get the coupons from the DB
    $coupon_rows = $general['db']->query(
      "SELECT c.coupon_id, c.name, c.code, ch.amount, " . $coupon_product_history_subquery . " as product_ids " .
      "FROM `" . DB_PREFIX . "coupon_history` as ch " .
      "LEFT JOIN `" . DB_PREFIX . "coupon` as c ON (ch.coupon_id=c.coupon_id) " .
      "WHERE ch.order_id='" . $general['db']->escape($order_row['order_id']) . "'"
    )->rows;

    foreach($coupon_rows as &$coupon_row) {
      if($coupon_row['product_ids']) {
        $coupon_row['product_ids'] = mb_split(',', $coupon_row['product_ids']);
      } else {
        $coupon_row['product_ids'] = array();
      }
    }

    return $coupon_rows;
  }
?>