<?php
  function get_expiration($order_product_row, $general) {

    // - order_product + product_option_value > product_option_value_to_moodle_period: today's date + interval * unit (expire date)
    // - how to determine renewal vs regular??
    // - take recurring into account
    // - how to determine if they bought 6 month or 12 month access??
    // - test with https://courses.biblemesh.com/hebrew/hebrew-first-steps-reading-1-bundle
    // - object extension (OR result duration)
  
    // option_id 13 (Registration Period), 14 (Registration Renewal Period)

    // get the expiration info from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT povtmp.interval, povtmp.unit, pov.option_id FROM `" . DB_PREFIX . "product_option_value_to_moodle_period` as povtmp " .
      "LEFT JOIN `" . DB_PREFIX . "product_option_value` as pov " .
        "ON (povtmp.product_option_value_id=pov.product_option_value_id AND povtmp.product_id=pov.product_id) " .
      "WHERE pov.product_id='" . $general['db']->escape($order_product_row['product_id']) . "' " .
        "AND pov.option_id IN ('13', '14')"
    )->row;

    if(!$product_moodle_mapping_row) return [];

    // TODO: $product_moodle_mapping_row['option_id'] == 14 ???

    switch($product_moodle_mapping_row['unit']) {
      case 'day':
      case 'month':
      case 'year':
        $expiretime = strtotime(date('Y-m-d') . ' + ' . $product_moodle_mapping_row['interval'] . ' ' . $product_moodle_mapping_row['unit'] . 's');
        break;
      default:
        return [];
    }

    return [
      "expires" => date('c', $expiretime)
    ];
  }
?>