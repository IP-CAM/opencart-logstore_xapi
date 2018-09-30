<?php
  function get_basic_context($log, $order_id, $general, $event_function) {

    return [
      "platform" => "OpenCart",
      "language" => $general['config_language'],
      "extensions" => [
        "http://lrs.learninglocker.net/define/extensions/info" => [
          "https://opencart.com" => VERSION,
          $general['plugin_url'] => $general['plugin_version'],
          "event_name" => $log['event_route'],
          "event_function" => $event_function,
          "order_id" => $order_id,
        ],
      ],
    ];
  }
?>