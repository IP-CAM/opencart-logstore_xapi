<?php
  function get_categories($order_product_row, $general) {

    // get the categories from the DB
    $category_rows = $general['db']->query(
      "SELECT cd.category_id, cd.name " .
      "FROM `" . DB_PREFIX . "product_to_category` as ptc " .
      "LEFT JOIN `" . DB_PREFIX . "category_description` as cd ON (ptc.category_id=cd.category_id) " .
      "WHERE ptc.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->rows;

    $categories = array();

    foreach($category_rows as $category_row) {
      $categories[] = [
        "id" => $category_row['category_id'],
        "definition" => [
          "type" => "http://id.tincanapi.com/activitytype/category",
          "name" => [
            $general['config_language'] => $category_row['name'],
          ],
        ],
        "objectType" => "Activity",
      ];
    }

    return $categories;
  }
?>