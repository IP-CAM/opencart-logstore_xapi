<?php
  require_once('get_product_options.php');
  require_once('get_ebooks.php');

  function get_product($order_row, $order_product_row, $general) {

    // get the moodle course id from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    // get the ebooks included in product
    $includedEbookInfo = get_ebooks($order_product_row, $general);

    // get the product page
    $productPage = $general['site_base'] . "index.php?route=product/product&product_id=" . $order_product_row['product_id'];

    // get info based on product type
    if($product_moodle_mapping_row) {  // it is a course
      $id = mb_ereg_replace('MOODLE_ID', $product_moodle_mapping_row['moodle_course_id'], $general['moodle_url_template']);
      $type = "http://id.tincanapi.com/activitytype/lms/course";

    } else if(count($includedEbookInfo) === 1) {  // it is a book
      $id = $includedEbookInfo[0]['id'];
      $type = "http://id.tincanapi.com/activitytype/book";

    } else {  // it is some other sort of product
      $id = $productPage;
      $type = "http://activitystrea.ms/schema/1.0/product";
    }

    $isRecurring = false;

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
          !(count($includedEbookInfo) === 1 && isset($includedEbookInfo[0]['isbn'])) ? [] : [
            "http://id.tincanapi.com/extension/isbn" => $includedEbookInfo[0]['isbn'],
          ]
        ),
      ],
    ];
  }
?>