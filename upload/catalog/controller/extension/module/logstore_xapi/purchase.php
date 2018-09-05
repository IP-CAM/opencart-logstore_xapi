<?php
  require_once('utils/get_customer.php');
  require_once('utils/get_course.php');

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
    $order_row = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='" . $general['db']->escape($order_id) . "'")->row;
    $order_product_rows = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id='" . $general['db']->escape($order_id) . "'")->rows;
    $actor = get_customer($log['customer_id'], $general);

    // see if we have all the info we need
    if(!$order_row) {
      echo "    Cannot find order row for purchase:\n";
      print_r($log);
      return;
    }
    if(count($order_product_rows) <= 0) {
      echo "    Cannot find order products for purchase:\n";
      print_r($log);
      return;
    }
    if(!$actor) {
      echo "    Cannot find customer who made the purchase:\n";
      print_r($log);
      return;
    }

    $statements = array();

    foreach($order_product_rows as $order_product_row) {
      $object = get_course($order_product_row, $general);

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
            "en" => "purchased",
          ],
        ],
        "object" => $object,
        "timestamp" => date('c', strtotime($order_row['date_added'])),
        "context" => [
          "platform" => "OpenCart",
          "language" => $general['config_language'],
          "extensions" => [
            "http://lrs.learninglocker.net/define/extensions/info" => [
              "https://opencart.com" => VERSION,
              "event_name" => $log['event_route'],
              "event_function" => "purchase",
              "order_id" => $order_id,
            ],
          ],
          "contextActivities" => [
            "grouping" => [
              [
                "id" => mb_ereg_replace('/$', '', $general['site_base']),
                "definition" => [
                  "type" => "http://activitystrea.ms/schema/1.0/service",
                  "name" => [
                    "en" => $general['config_name'],
                  ],
                ],
                "objectType" => "Activity"
              ],
            ],
            "category" => [
              [
                "id" => "https://opencart.com",
                "definition" => [
                  "type" => "http://id.tincanapi.com/activitytype/source",
                  "name" => [
                    "en" => "OpenCart",
                  ],
                ],
                "objectType" => "Activity",
              ],
            ],
          ],
        ],
      ];
    }

    return $statements;

  }
?>