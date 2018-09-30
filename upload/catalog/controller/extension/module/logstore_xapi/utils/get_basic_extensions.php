<?php
  function get_basic_extensions($log, $general, $event_function) {

    return [
      "http://lrs.learninglocker.net/define/extensions/info" => [
        "https://opencart.com" => VERSION,
        $general['plugin_url'] => $general['plugin_version'],
        "event_name" => $log['event_route'],
        "event_function" => $event_function,
      ],
    ];
  }
?>