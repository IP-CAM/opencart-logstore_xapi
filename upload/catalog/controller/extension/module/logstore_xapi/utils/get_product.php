<?php
  require_once('get_product_options.php');

  function get_product($order_row, $order_product_row, $general) {

    // get the moodle course id from the DB
    $product_moodle_mapping_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "product_moodle_mapping` " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    // get the ebooks included in product
    $product_attribute_row = $general['db']->query(
      "SELECT pa.text FROM `" . DB_PREFIX . "product_attribute` as pa " .
      "LEFT JOIN `" . DB_PREFIX . "attribute` as a ON (pa.attribute_id=a.attribute_id) " .
      "LEFT JOIN `" . DB_PREFIX . "attribute_description` as ad " .
        "ON (pa.attribute_id=ad.attribute_id AND ad.language_id='" . $general['language_id'] . "') " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "' " .
        "AND a.attribute_group_id='" . $general['ebook_attribute_group_id'] . "' " .
        "AND ad.name='" . $general['ebook_attribute_description_name'] . "'"
    )->row;
    $includedEbookURLs = array();
    if($product_attribute_row) {
      $ebookIds = mb_split(' ', $product_attribute_row['text']);
      foreach($ebookIds as $ebookId) {
        $includedEbookURLs[] = mb_ereg_replace('EBOOK_ID', $ebookId, $general['ebook_url_template']);
      }
    }

    // get the product page
    $productPage = $general['site_base'] . "index.php?route=product/product&product_id=" . $order_product_row['product_id'];

    // get info based on product type
    if($product_moodle_mapping_row) {  // it is a course
      $id = mb_ereg_replace('MOODLE_ID', $product_moodle_mapping_row['moodle_course_id'], $general['moodle_url_template']);
      $type = "http://id.tincanapi.com/activitytype/lms/course";

    } else if(count($includedEbookURLs) === 1) {  // it is a book
      $id = $includedEbookURLs[0];
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
          count($includedEbookURLs) === 0 ? [] : [
            "http://lrs.resourcingeducation.com/extension/included-ebooks" => $includedEbookURLs,
          ]
        ),
      ],
    ];
  }
?>