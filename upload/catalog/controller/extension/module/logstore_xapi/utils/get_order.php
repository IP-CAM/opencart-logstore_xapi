<?php
  require_once('get_coupons.php');

  function get_order($order_row, $order_product_row, $general) {

    // get the order totals from the DB
    $order_total_rows = $general['db']->query(
      "SELECT ot.code, ot.value " .
      "FROM `" . DB_PREFIX . "order_total` as ot " .
      "WHERE ot.order_id='" . $general['db']->escape($order_row['order_id']) . "' " .
        "AND ot.code IN ('sub_total', 'total') "
    )->rows;

    $totalinfo = array();

    foreach($order_total_rows as $order_total_row) {
      $totalinfo[$order_total_row['code']] = $order_total_row['value'];
    }

    $order = [
      "id" => $order_row['order_id'],
    ];
    
    if(isset($totalinfo['sub_total'])) {
      $order["subtotal"] = $totalinfo['sub_total'];
    }
    
    $coupons = get_coupons($order_row, $order_product_row, $general);

    if(count($coupons) > 0) {
      $order["coupons"] = $coupons;
    }

    if(isset($totalinfo['total'])) {
      $order["total"] = $totalinfo['total'];
    }

    return [
      "http://lrs.resourcingeducation.com/extension/order" => $order,
    ];
  }
?>