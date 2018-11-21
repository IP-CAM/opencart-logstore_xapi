<?php
  require_once('get_product_options.php');
  require_once('get_ebooks.php');

  function get_product($order_row, $order_product_row, $coupon_rows, $totalProductPrices, $isRefund, $general) {

    // get the moodle course id from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    // get the ebooks included in product
    $includedEbookInfo = get_ebooks($order_product_row, $general);

    // get the product page
    $productPage = $general['site_base'] . "index.php?route=product/product&product_id=" . $order_product_row['product_id'];
    $isEbook = false;
    
    // get info based on product type
    if($product_moodle_mapping_row) {  // it is a course
      $id = mb_ereg_replace('MOODLE_ID', $product_moodle_mapping_row['moodle_course_id'], $general['moodle_url_template']);
      $type = "http://id.tincanapi.com/activitytype/lms/course";

    } else if(count($includedEbookInfo) === 1) {  // it is a book
      $isEbook = true;
      $id = $includedEbookInfo[0]['id'];
      $type = "http://id.tincanapi.com/activitytype/book";

    } else {  // it is some other sort of product
      $id = $productPage;
      $type = "http://activitystrea.ms/schema/1.0/product";
    }

    $isRecurring = false;

    $cost = $order_product_row['price'];

    // Figure it out if the coupon clearly relates to this product only, else divide it
    // between the products
    foreach($coupon_rows as $coupon_row) {
      if(in_array($order_product_row['product_id'], $coupon_row['product_ids'])) {
        $cost += $coupon_row['amount'];

      } else if(count($coupon_row['product_ids']) === 0) {
        $cost += $coupon_row['amount'] * ($order_product_row['price'] / $totalProductPrices);
      }
    }

    if($isRefund) {
      $cost *= -1;
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
            "http://lrs.resourcingeducation.com/extension/price" => $order_product_row['price'],
            "http://lrs.resourcingeducation.com/extension/cost" => $cost,
          ],
          ($isRecurring
            ? [
              "http://lrs.resourcingeducation.com/extension/recurring-subscription" => [
                "id" => 123,
                "period" => "??",
              ]
            ]
            : array()
          ),
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