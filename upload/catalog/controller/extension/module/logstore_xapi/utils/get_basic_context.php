<?php
  function get_basic_context($log, $order_id, $general) {

    return [
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
    ];
  }
?>