<?php
  require_once('utils/format_language.php');
  require_once('utils/get_customer.php');
  require_once('utils/get_course.php');
  require_once('utils/get_basic_context.php');
  require_once('utils/get_partner.php');
  require_once('utils/get_site.php');
  require_once('utils/get_platform.php');
  require_once('utils/get_categories.php');
  require_once('utils/get_institution.php');
  require_once('utils/get_affiliate.php');
  require_once('utils/get_coupons.php');

  function purchase($log, $general) {

    // get the order id
    $data = json_decode($log['data']);
    $order_id = $data[0];

    if(!$order_id) {
      echo "    Invalid purchase log:\n";
      print_r($log);
      return;
    }

    // get the info needed from the DB
    $order_row = $general['db']->query(
      "SELECT o.*, l.code as language_code " .
      "FROM `" . DB_PREFIX . "order` as o " .
      "LEFT JOIN `" . DB_PREFIX . "language` as l ON (o.language_id=l.language_id) " .
      "WHERE o.order_id='" . $general['db']->escape($order_id) . "'"
    )->row;
    if(!$order_row) {
      echo "    Cannot find order row for purchase:\n";
      print_r($log);
      return;
    }
    $orig_config_language = $general['config_language'];
    $general['config_language'] = format_language($order_row['language_code']);

    $order_product_rows = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "order_product` " .
      "WHERE order_id='" . $general['db']->escape($order_id) . "'"
    )->rows;
    if(count($order_product_rows) <= 0) {
      echo "    Cannot find order products for purchase:\n";
      print_r($log);
      return;
    }

    $actor = get_customer($log['customer_id'], $general);
    if(!$actor) {
      echo "    Cannot find customer who made the purchase:\n";
      print_r($log);
      return;
    }

    $statements = array();

    foreach($order_product_rows as $order_product_row) {
      $object = get_course($order_row, $order_product_row, $general);

      if(!$object) {
        echo "    Skipping, as this is not a course:\n";
        print_r($log);
        continue;
      }

      $statements[] = [
        "actor" => $actor,
        "verb" => [
          "id" => "http://activitystrea.ms/schema/1.0/purchase",
          "display" => [
            $general['config_language'] => "purchased",
          ],
        ],
        "object" => $object,
        "timestamp" => date('c', strtotime($order_row['date_added'])),
        "context" => array_merge(
          get_basic_context($log, $order_id, $general, "purchase"),
          [
            "contextActivities" => [
              "parent" => array_merge(
                get_partner($order_product_row, $general)
              ),
              "grouping" => array_merge(
                get_site($general)
              ),
              "category" => array_merge(
                get_platform($general),
                get_categories($order_product_row, $general)
              ),
              "other" => array_merge(
                get_institution($order_row, $general)
              ),
            ],
            "extensions" => array_merge(
              get_affiliate($order_row['affiliate_id'], $general),
              get_coupons($order_row, $order_product_row, $general)
            ),
          ]
        ),
      ];
    }

    $general['config_language'] = $orig_config_language;

    return $statements;

  }
?>