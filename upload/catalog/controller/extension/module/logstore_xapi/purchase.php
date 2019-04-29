<?php
  require_once('utils/format_language.php');
  require_once('utils/get_customer.php');
  require_once('utils/get_coupon_rows.php');
  require_once('utils/get_product.php');
  require_once('utils/get_basic_context.php');
  require_once('utils/get_partner.php');
  require_once('utils/get_site.php');
  require_once('utils/get_platform.php');
  require_once('utils/get_categories.php');
  require_once('utils/get_institution.php');
  require_once('utils/get_basic_extensions.php');
  require_once('utils/get_affiliate.php');
  require_once('utils/get_order.php');

  function purchase($log, $general) {

    // get the order id
    $statements = array();
    $data = json_decode($log['data'], true);
    $order_id = intval($data[0]);
    $order_status_id = intval($data[1]);

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
    if($order_row['language_id'] != $general['language_id']) {
      echo "    Invalid order language:\n";
      print_r($log);
      return;
    }

    $general['language_code'] = format_language($order_row['language_code']);

    $isOrdered = in_array($order_status_id, $general['ordered_order_status_ids']);
    $isCanceled = in_array($order_status_id, $general['canceled_order_status_ids']);
    $isComplete = in_array($order_status_id, $general['completed_order_status_ids']);
    $isRefund = in_array($order_status_id, $general['refunded_order_status_ids']);

    // If it is complete, look up order history. If it was never "ordered," then first do that xAPI statement.
    if($isComplete) {

      $order_history_rows = $general['db']->query(
        "SELECT order_id FROM `" . DB_PREFIX . "order_history` " .
        "WHERE order_id='" . $general['db']->escape($order_id) . "' " .
        "AND order_status_id IN (" . implode(',', $general['ordered_order_status_ids']) . ")"
      )->rows;

      if(count($order_history_rows) == 0) {
        $statements = purchase(
          [
            "event_route" => $log['event_route'],
            "date_added" => $log['date_added'],
            "data" => json_encode([
              $order_id,
              $general['ordered_order_status_ids'][0],
            ]),
          ],
          $general
        );
      }
    }

    $verb = false;

    if($isOrdered) {
      $verb = [
        "id" => "http://lrs.resourcingeducation.com/verb/order",
        "display" => [
          $general['language_code'] => "ordered",
        ],
      ];

    } elseif($isComplete) {
      $verb = [
        "id" => "http://activitystrea.ms/schema/1.0/purchase",
        "display" => [
          $general['language_code'] => "purchased",
        ],
      ];

    } elseif($isCanceled) {
      $verb = [
        "id" => "http://lrs.resourcingeducation.com/verb/cancel",
        "display" => [
          $general['language_code'] => "canceled order for",
        ],
      ];

    } elseif($isRefund) {
      $verb = [
        "id" => "http://lrs.resourcingeducation.com/verb/refunded",
        "display" => [
          $general['language_code'] => "was refunded for",
        ],
      ];

    }

    if(!$verb) {
      echo "    Discarding order id " . $order_id . " due to order status id of " . $order_status_id . ". (Current order status id is " . $order_row['order_status_id'] . ".)\n";
      return 'discard log';
    }

    $order_product_rows = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "order_product` " .
      "WHERE order_id='" . $general['db']->escape($order_id) . "'"
    )->rows;
    if(count($order_product_rows) <= 0) {
      echo "    Cannot find order products for purchase:\n";
      print_r($log);
      return;
    }

    $actor = get_customer($order_row['customer_id'], $general);
    if(!$actor) {
      echo "    Cannot find customer who made the purchase:\n";
      print_r($log);
      return;
    }

    $coupon_rows = get_coupon_rows($order_row, $general);
    
    $totalProductPrices = 0;
    foreach($order_product_rows as $order_product_row) {
      $totalProductPrices += $order_product_row['total'];
    }

    foreach($order_product_rows as $order_product_row) {
      $object = get_product($order_row, $order_product_row, $coupon_rows, $totalProductPrices, $isRefund, $general);

      if(!$object) {
        echo "    Skipping—product not found.\n";
        print_r($log);
        continue;
      }

      $timestampDateTime = new DateTime($log['date_added'], new DateTimeZone('UTC'));

      $statements[] = $newstatement = [
        "actor" => $actor,
        "verb" => $verb,
        "object" => $object,
        "timestamp" => $timestampDateTime->format('c'),
        "context" => array_merge(
          get_basic_context($general),
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
              get_basic_extensions($log, $general, "purchase"),
              get_order($order_row, $coupon_rows, $general),
              get_affiliate($order_row['affiliate_id'], $general)
            ),
          ]
        ),
      ];

      foreach($newstatement['context']['contextActivities'] as $type => $contents) {
        if(count($contents) === 0) {
          unset($statements[count($statements)-1]['context']['contextActivities'][$type]);
        }
      }
    }

    return $statements;

  }
?>