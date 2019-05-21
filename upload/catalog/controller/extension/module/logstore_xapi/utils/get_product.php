<?php
  require_once('get_product_options.php');
  require_once('get_ebooks.php');

  function get_product($order_row, $order_product_row, $coupon_rows, $totalProductPrices, $isRefund, $general, $amount=null) {

    // get the moodle course ids from the DB
    $product_moodle_mapping_rows = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->rows;
    $includedCourses = [];
    foreach($product_moodle_mapping_rows as $product_moodle_mapping_row) {
      $includedCourses[] = mb_ereg_replace('MOODLE_ID', $product_moodle_mapping_row['moodle_course_id'], $general['moodle_url_template']);
    }

    // get the ebooks included in product
    $includedEbookInfo = get_ebooks($order_product_row, $general);

    // get the product page
    $productPage = $general['site_base'] . "index.php?route=product/product&product_id=" . $order_product_row['product_id'];
    $isEbook = false;
    
    // get info based on product type
    if(count($includedCourses) === 1) {  // it is a course
      $id = $includedCourses[0];
      $type = "http://id.tincanapi.com/activitytype/lms/course";

    } else if(count($includedCourses) === 0 && count($includedEbookInfo) === 1) {  // it is a book
      $isEbook = true;
      $id = $includedEbookInfo[0]['id'];
      $type = "http://id.tincanapi.com/activitytype/book";

    } else {  // it is some other sort of product
      $id = $productPage;
      $type = "http://activitystrea.ms/schema/1.0/product";
    }

    // get the recurring info
    $recurringInfo = array();
    $order_recurring_rows = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "order_recurring` as or1 " .
      "WHERE order_id='" . $general['db']->escape($order_row['order_id']) . "' " .
        "AND product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->rows;
    foreach($order_recurring_rows as $order_recurring_row) {
      $recurringInfo[] = [
        "id" => $order_recurring_row['recurring_id'],
        "name" => $order_recurring_row['recurring_name'],
        "frequency" => $order_recurring_row['recurring_frequency'],
        "cycle" => $order_recurring_row['recurring_cycle'],
        "duration" => $order_recurring_row['recurring_duration'],
        "price" => $order_recurring_row['recurring_price'],
      ];

      if($order_recurring_row['trial'] == "1") {
        $idx = count($recurringInfo) - 1;
        $recurringInfo[$idx]["trial"] = [
          "frequency" => $order_recurring_row['trial_frequency'],
          "cycle" => $order_recurring_row['trial_cycle'],
          "duration" => $order_recurring_row['trial_duration'],
          "price" => $order_recurring_row['trial_price'],
        ];
      }
    }

    if(isset($amount)) {
      $cost = $price = $amount;

    } else {
      $cost = $order_product_row['price'];
  
      // Figure it out if the coupon clearly relates to this product only, else divide it
      // between the products
      foreach($coupon_rows as $coupon_row) {
        if(in_array($order_product_row['product_id'], $coupon_row['product_ids'])) {
          $cost += $coupon_row['amount'];
  
        } else if(count($coupon_row['product_ids']) === 0) {
          $totalPrice = $totalProductPrices ? $totalProductPrices : $order_product_row['price'];
          $cost += $coupon_row['amount'] * ($order_product_row['price'] / $totalProductPrices);
        }
      }
  
      if($isRefund) {
        $cost *= -1;
      }

      $price = $order_product_row['price'];
    }


    return [
      "id" => $id,
      "definition" => [
        "type" => $type,
        "name" => [
          $general['language_code'] => $order_product_row['name'],
        ],
        "moreInfo" => $productPage,
        "extensions" => array_merge(
          get_product_options($order_row, $order_product_row, $general),
          [
            "http://lrs.resourcingeducation.com/extension/price" => $price,
            "http://lrs.resourcingeducation.com/extension/cost" => $cost,
          ],
          (count($recurringInfo) > 0
            ? [
              "http://lrs.resourcingeducation.com/extension/recurring-subscriptions" => $recurringInfo,
            ]
            : array()
          ),
          count($includedCourses) === 0 ? [] : [
            "http://lrs.resourcingeducation.com/extension/included-courses" => $includedCourses,
          ],
          count($includedEbookInfo) === 0 ? [] : [
            "http://lrs.resourcingeducation.com/extension/included-ebooks" => $includedEbookInfo,
          ],
          !($isEbook && isset($includedEbookInfo[0]['isbn'])) ? [] : [
            "http://id.tincanapi.com/extension/isbn" => $includedEbookInfo[0]['isbn'],
          ]
        ),
      ],
    ];
  }
?>