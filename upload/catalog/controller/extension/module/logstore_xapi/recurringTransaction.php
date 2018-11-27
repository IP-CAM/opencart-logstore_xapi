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

  function recurringTransaction($log, $general) {

    $data = json_decode($log['data'], true);
    $oneMinutePastDateAdded = date('Y-m-d H:i:s', strtotime($log['date_added']) + 60);

    if(
      count($data) === 2
      && $data[1] === 'IPN data'
      && is_array($data[0])
      && isset($data[0]['request'])
      && isset($data[0]['response'])
    ) {
      // Coming from catalog/model/extension/payment/pp_express/log

      $request = $data[0]['request'];
      $response = $data[0]['response'];
  
      if($response !== "VERIFIED" || !isset($request['post']['txn_type'])) {
        return 'discard log';
      }
  
      $transactionTypes = [
        'recurring_payment' => "1",
        'recurring_payment_suspended' => "6",
        'recurring_payment_suspended_due_to_max_failed_payment' => "7",
        'recurring_payment_failed' => "4",
        'recurring_payment_outstanding_payment_failed' => "8",
        'recurring_payment_outstanding_payment' => "2",
        // 'recurring_payment_recurring_date_added' => "0",
        'recurring_payment_recurring_cancel' => "5",
        // 'recurring_payment_skipped' => "3",
        'recurring_payment_expired' => "9",
      ];

      if(!isset($transactionTypes[$request['post']['txn_type']])) {
        return 'discard log';
      }

      $order_recurring_id = $request['post']['recurring_payment_id'];
      $typeName = $request['post']['txn_type'];
      $type = $transactionTypes[$typeName];
  
    } else if(
      count($data) === 2
      && is_int($data[0])
      && is_int($data[1])
      // && mb_ereg_match('^[0-9]+$', $data[0])
      // && mb_ereg_match('^[0-9]+$', $data[1])
    ) {
      // Coming from catalog/model/account/recurring/addOrderRecurringTransaction

      $order_recurring_id = $data[0];
      $type = $data[1];
      $typeName = array_search($type, $transactionTypes);

      if(!$typeName) {
        return 'discard log';
      }

    } else {
      return 'discard log';
    }

    // Look for the first transaction matching order_recurring_id and type that was after
    // (or equal to) the date_added of the log, so long as it is no more than a minute later.
    $order_recurring_transaction_row = $general['db']->query(
      "SELECT ort.*, or1.order_id, or1.product_id " .
      "FROM `" . DB_PREFIX . "order_recurring_transaction` as ort " .
      "LEFT JOIN `" . DB_PREFIX . "order_recurring` as or1 ON (or1.order_recurring_id=ort.order_recurring_id) " .
      "WHERE ort.order_recurring_id='" . $order_recurring_id . "' " .
        "AND ort.type='" . $type . "' " .
        "AND ort.date_added>='" . $log['date_added'] . "' " .
        "AND ort.date_added<'" . $oneMinutePastDateAdded . "' " .
      "ORDER BY ort.date_added " . 
      "LIMIT 1 "
    )->row;


    if(!$order_recurring_transaction_row) {
      echo "    Cannot find order_recurring_transaction row:\n";
      print_r($log);
      return;
    }

    // get the order id
    $order_id = intval($order_recurring_transaction_row['order_id']);

    if(!$order_id) {
      echo "    Invalid recurring transaction log:\n";
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
      echo "    Cannot find order row for recurring transaction:\n";
      print_r($log);
      return;
    }
    if($order_row['language_id'] != $general['language_id']) {
      echo "    Invalid recurring transaction language:\n";
      print_r($log);
      return;
    }

    $general['language_code'] = format_language($order_row['language_code']);

    $recurringPurchaseVerb = [
      "id" => "http://lrs.resourcingeducation.com/verb/paid-recurring",
      "display" => [
        $general['language_code'] => "made recurring payment for",
      ],
    ];

    $recurringCancelVerb = [
      "id" => "http://lrs.resourcingeducation.com/verb/cancel-recurring",
      "display" => [
        $general['language_code'] => "canceled recurring payment for",
      ],
    ];

    $recurringFailureVerb = [
      "id" => "http://lrs.resourcingeducation.com/verb/error-recurring",
      "display" => [
        $general['language_code'] => "failed to execute recurring for",
      ],
    ];

    $transactionVerbs = [
      "1" => $recurringPurchaseVerb,
      "6" => $recurringFailureVerb,
      "7" => $recurringFailureVerb,
      "4" => $recurringFailureVerb,
      "8" => $recurringFailureVerb,
      "2" => $recurringPurchaseVerb,
      // "0" => "",
      "5" => $recurringCancelVerb,
      // "3" => "",
      "9" => $recurringFailureVerb,
    ];

    $order_product_row = $general['db']->query(
      "SELECT * FROM `" . DB_PREFIX . "order_product` " .
      "WHERE product_id='" . $general['db']->escape($order_recurring_transaction_row['product_id']) . "' " .
        "AND order_id='" . $general['db']->escape($order_id) . "'"
    )->row;
    if(!$order_product_row) {
      echo "    Cannot find order products for recurring transaction:\n";
      print_r($log);
      return;
    }

    $actor = get_customer($order_row['customer_id'], $general);
    if(!$actor) {
      echo "    Cannot find customer of the recurring transaction:\n";
      print_r($log);
      return;
    }

    $statements = array();

    $coupon_rows = get_coupon_rows($order_row, $general);

    // Don't sent $coupon_rows to get_product because this is not the initial transaction.
    $object = get_product($order_row, $order_product_row, array(), null, false, $general);

    if(!$object) {
      echo "    Skipping—product not found.\n";
      print_r($log);
      return;
    }

    $statements[] = $newstatement = [
      "actor" => $actor,
      "verb" => $transactionVerbs[$type],
      "object" => $object,
      "timestamp" => date('c', strtotime($order_row['date_added'])),
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
            get_basic_extensions($log, $general, "recurringTransaction"),
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

    return $statements;

  }
?>
