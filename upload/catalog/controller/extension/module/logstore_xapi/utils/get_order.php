<?php
  
  function get_order($order_row, $coupon_rows, $general) {

    // get the order totals from the DB
    $order_total_row = $general['db']->query(
      "SELECT ot.value " .
      "FROM `" . DB_PREFIX . "order_total` as ot " .
      "WHERE ot.order_id='" . $general['db']->escape($order_row['order_id']) . "' " .
        "AND ot.code='" . $general['db']->escape('sub_total') . "'"
    )->row;

    $order = [
      "id" => $order_row['order_id'],
      "total" => $order_row['total'],
      "status_id" => $order_row['order_status_id'],
    ];
    
    if($order_total_row) {
      $order["subtotal"] = $order_total_row['value'];
    }
    
    if(count($coupon_rows) > 0) {

      // Get all coupons related to this order

      $order["coupons"] = array();

      foreach($coupon_rows as $coupon_row) {
        $order["coupons"][] = [
          "id" => $coupon_row['coupon_id'],
          "code" => $coupon_row['code'],
          "name" => $coupon_row['name'],
          "effective_amount" => $coupon_row['amount'],
        ];
      }
    
    }

    return [
      "http://lrs.resourcingeducation.com/extension/order" => $order,
    ];
  }
?>