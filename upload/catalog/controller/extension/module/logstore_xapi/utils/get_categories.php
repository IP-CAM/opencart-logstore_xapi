<?php
  function get_categories($order_product_row, $general) {

    // get the categories from the DB
    $category_rows = $general['db']->query(
      "SELECT cd.category_id, cd.name " .
      "FROM `" . DB_PREFIX . "product_to_category` as ptc " .
      "LEFT JOIN `" . DB_PREFIX . "category_description` as cd " .
        "ON (ptc.category_id=cd.category_id AND cd.language_id='" . $general['language_id'] . "') " .
      "WHERE ptc.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->rows;

    $categories = array();

    foreach($category_rows as $category_row) {
      
      $categoryPage = $general['site_base'] . "index.php?route=product/category&path=" . $category_row['category_id'];

      $categories[] = [
        "id" => $categoryPage,
        "definition" => [
          "type" => "http://id.tincanapi.com/activitytype/category",
          "name" => [
            $general['language_code'] => $category_row['name'],
          ],
        ],
        "objectType" => "Activity",
      ];
    }

    return $categories;
  }
?>